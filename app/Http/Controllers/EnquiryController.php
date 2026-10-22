<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Enquiry;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\SysUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/**
 * Admin listing for enquiries posted in by the external site.
 */
class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->has('clear_filters')) {
            return redirect()->route('enquiries.index');
        }

        $query = Enquiry::with(['standard', 'educationalBoard', 'assignee']);
        $this->applyFilters($request, $query);

        $sortBy = in_array($request->get('sort_by'), ['created_at', 'first_name', 'status'], true)
            ? $request->get('sort_by')
            : 'created_at';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';

        $enquiries = $query->orderBy($sortBy, $sortOrder)->paginate(20)->withQueryString();

        // Unfiltered counts per status for the tab strip.
        $statusCounts = [];
        foreach (Enquiry::statuses() as $status) {
            $statusCounts[$status] = Enquiry::where('status', $status)->count();
        }

        return view('enquiries.index', [
            'enquiries' => $enquiries,
            'statuses' => Enquiry::statuses(),
            'statusCounts' => $statusCounts,
            'totalCount' => array_sum($statusCounts),
            'standards' => Standard::orderBy('std_id')->get(),
            'boards' => EducationalBoard::orderBy('eduboard_id')->get(),
            'assignees' => SysUser::orderBy('fname')->get(),
        ]);
    }

    public function show(Enquiry $enquiry)
    {
        $enquiry->load(['standard', 'educationalBoard', 'assignee', 'convertedStudent']);

        return view('enquiries.show', [
            'enquiry' => $enquiry,
            'statuses' => Enquiry::statuses(),
            'assignees' => SysUser::orderBy('fname')->get(),
        ]);
    }

    public function updateStatus(Request $request, Enquiry $enquiry)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Enquiry::statuses()),
            'assigned_to' => 'nullable|integer|exists:sysuser,sysuser_id',
            'remarks' => 'nullable|string|max:2000',
        ]);

        $wasNew = $enquiry->status === Enquiry::STATUS_NEW;

        $enquiry->update([
            'status' => $validated['status'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'contacted_at' => ($wasNew && $validated['status'] !== Enquiry::STATUS_NEW)
                ? now()
                : $enquiry->contacted_at,
        ]);

        return redirect()->route('enquiries.show', $enquiry->enquiry_id)
            ->with('success', 'Enquiry updated successfully.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('enquiries.index')
            ->with('success', 'Enquiry removed.');
    }

    public function export(Request $request)
    {
        $query = Enquiry::with(['standard', 'educationalBoard', 'assignee']);
        $this->applyFilters($request, $query);
        $enquiries = $query->orderBy('created_at', 'desc')->get();

        $filename = 'enquiries-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($enquiries) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Enquiry ID', 'Source', 'Name', 'Email', 'Phone', 'WhatsApp', 'Gender',
                'Standard', 'Board', 'Medium', 'Stream', 'School',
                'City/Village', 'Taluka', 'District', 'State', 'Pincode',
                'Guardian Name', 'Guardian Contact', 'Language', 'Message',
                'Status', 'Assigned To', 'Received On',
            ]);

            foreach ($enquiries as $enquiry) {
                fputcsv($handle, [
                    $enquiry->enquiry_id,
                    $enquiry->source,
                    $enquiry->full_name,
                    $enquiry->email,
                    $enquiry->phone,
                    $enquiry->whatsapp_no,
                    $enquiry->gender,
                    optional($enquiry->standard)->stdname ?? $enquiry->standard_name,
                    optional($enquiry->educationalBoard)->boardname ?? $enquiry->board_name,
                    $enquiry->medium,
                    $enquiry->stream,
                    $enquiry->school_name,
                    $enquiry->city_village,
                    $enquiry->taluka_name,
                    $enquiry->district_name,
                    $enquiry->state_name,
                    $enquiry->pincode,
                    $enquiry->guardian_name,
                    $enquiry->guardian_contact,
                    $enquiry->selected_language,
                    $enquiry->message,
                    $enquiry->status,
                    $enquiry->assignee ? trim($enquiry->assignee->fname . ' ' . $enquiry->assignee->lname) : '',
                    $enquiry->created_at ? $enquiry->created_at->format('d/m/Y H:i') : '',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    protected function applyFilters(Request $request, $query): void
    {
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->get('source'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->get('assigned_to'));
        }

        if ($request->filled('std_id')) {
            $query->whereIn('std_id', (array) $request->get('std_id'));
        }

        if ($request->filled('eduboard_id')) {
            $query->whereIn('eduboard_id', (array) $request->get('eduboard_id'));
        }

        if ($request->filled('city_village')) {
            $query->where('city_village', 'like', '%' . $request->get('city_village') . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', DateHelper::convertDateFormat($request->get('start_date')));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', DateHelper::convertDateFormat($request->get('end_date')));
        }
    }
}
