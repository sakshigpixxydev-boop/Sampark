<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Public intake endpoint for enquiries posted by an external website.
 *
 * Guarded by the api.key middleware and a rate limit; see docs/ENQUIRY_API.md.
 */
class EnquiryApiController extends Controller
{
    /**
     * A caller that retries will not create a second row within this window.
     */
    private const DUPLICATE_WINDOW_MINUTES = 10;

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150|required_without:phone',
            'phone' => 'nullable|string|max:20|required_without:email',
            'whatsapp_no' => 'nullable|string|max:20',
            'gender' => 'nullable|in:M,F,O,Male,Female,Other,male,female,other',
            'birthdate' => 'nullable|date',
            'standard' => 'nullable|string|max:100',
            'board' => 'nullable|string|max:100',
            'medium' => 'nullable|string|max:50',
            'stream' => 'nullable|in:Arts,Commerce,Science',
            'school_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'taluka' => 'nullable|string|max:100',
            'city_village' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'pincode' => 'nullable|digits:6',
            'guardian_name' => 'nullable|string|max:200',
            'guardian_contact' => 'nullable|string|max:20',
            'guardian_relation' => 'nullable|string|max:50',
            'language' => 'nullable|in:en,hi,gu',
            'message' => 'nullable|string|max:2000',
            'source' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            // A retrying caller should not create duplicates.
            if (!empty($data['email'])) {
                $recent = Enquiry::where('email', $data['email'])
                    ->where('created_at', '>=', now()->subMinutes(self::DUPLICATE_WINDOW_MINUTES))
                    ->first();

                if ($recent) {
                    return response()->json([
                        'success' => true,
                        'duplicate' => true,
                        'message' => 'Enquiry already received.',
                        'data' => [
                            'enquiry_id' => $recent->enquiry_id,
                            'status' => $recent->status,
                        ],
                    ], 200);
                }
            }

            $enquiry = Enquiry::create([
                'source' => $data['source'] ?? 'external_api',

                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'whatsapp_no' => $data['whatsapp_no'] ?? null,
                'gender' => $this->normaliseGender($data['gender'] ?? null),
                'birthdate' => $data['birthdate'] ?? null,

                'standard_name' => $data['standard'] ?? null,
                'std_id' => $this->resolveId('ms_standard', 'stdname', 'std_id', $data['standard'] ?? null),
                'board_name' => $data['board'] ?? null,
                'eduboard_id' => $this->resolveId('ms_eductionalboard', 'boardname', 'eduboard_id', $data['board'] ?? null),
                'medium' => $data['medium'] ?? null,
                'stream' => $data['stream'] ?? null,
                'school_name' => $data['school_name'] ?? null,

                'country_name' => $data['country'] ?? null,
                'state_name' => $data['state'] ?? null,
                'state_id' => $this->resolveId('ms_state', 'sname', 'state_id', $data['state'] ?? null),
                'district_name' => $data['district'] ?? null,
                'dist_id' => $this->resolveId('ms_dist', 'dname', 'dist_id', $data['district'] ?? null),
                'taluka_name' => $data['taluka'] ?? null,
                'takula_id' => $this->resolveId('ms_taluko', 'tname', 'takula_id', $data['taluka'] ?? null),
                'city_village' => $data['city_village'] ?? null,
                'address' => $data['address'] ?? null,
                'pincode' => $data['pincode'] ?? null,

                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_contact' => $data['guardian_contact'] ?? null,
                'guardian_relation' => $data['guardian_relation'] ?? null,

                'selected_language' => $data['language'] ?? 'en',
                'message' => $data['message'] ?? null,
                'status' => Enquiry::STATUS_NEW,

                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'raw_payload' => $request->all(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry received.',
                'data' => [
                    'enquiry_id' => $enquiry->enquiry_id,
                    'status' => $enquiry->status,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Enquiry intake failed: ' . $e->getMessage(), [
                'ip' => $request->ip(),
            ]);

            // Never leak internals to an external caller.
            return response()->json([
                'success' => false,
                'message' => 'Unable to record enquiry.',
            ], 500);
        }
    }

    /**
     * Map whatever the caller sent onto the single-character column.
     */
    private function normaliseGender(?string $gender): ?string
    {
        if (blank($gender)) {
            return null;
        }

        return match (strtoupper(substr($gender, 0, 1))) {
            'M' => 'M',
            'F' => 'F',
            default => 'O',
        };
    }

    /**
     * Resolve free text against a master-data table. Exact (case-insensitive)
     * match only — anything else stays NULL and the posted text is kept in the
     * matching *_name column.
     */
    private function resolveId(string $table, string $nameColumn, string $idColumn, ?string $value)
    {
        if (blank($value)) {
            return null;
        }

        return DB::table($table)
            ->whereRaw("LOWER({$nameColumn}) = ?", [strtolower(trim($value))])
            ->value($idColumn);
    }
}
