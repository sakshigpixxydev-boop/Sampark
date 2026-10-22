<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Student;
use App\Models\Event;
use App\Models\SemesterBatch;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateHelper;

class CertificateController extends Controller
{
    public function index()
    {
        $query = Certificate::with('batch.standard');

        $this->applySchoolScope($query);

        $certificates = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('certificates.index', compact('certificates'));
    }

    public function create()
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        // Only get students with completed payment and at least one completed semester batch
        $students = Student::where('fees_status', '=', 'PAID', 'and')
            ->whereHas('semesterBatches', function ($query) {
                $query->where('student_sembatches.status', 1); // 1 = Completed
            })
            ->when($schoolIds !== null, fn ($q) => $q->whereIn('school_id', $schoolIds))
            ->orderBy('fname')
            ->get();

        $events = Event::when($schoolIds !== null, fn ($q) => $q->whereIn('school_id', $schoolIds))
            ->orderBy('title')
            ->get();
        
        // Optionally only show completed programs/batches, but better to allow selection
        // of any batch as long as the student has completed it.
        $programs = SemesterBatch::with('standard')->orderBy('start_date', 'desc')->get();

        return view('certificates.create', compact('students', 'events', 'programs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:STUDENT,EVENT,PROGRAM',
            'sembatches_id' => 'required|exists:sembatches,sembatches_id',
            'recipient_name' => 'required|string|max:255',
            'generated_date' => 'required|date_format_dmy',
        ]);

        $generatedDate = DateHelper::convertDateFormat($request->generated_date);
        $request->merge(['generated_date' => $generatedDate]);

        $batch = SemesterBatch::findOrFail($request->sembatches_id);
        
        // Determine reference ID based on type
        if ($request->type === 'STUDENT') {
            $reference_id = $request->reference_id_student;
            
            // Server-side strict restriction for standard flow
            $student = Student::allCohorts()->findOrFail($reference_id);

            $schoolIds = Auth::user()->restrictedSchoolIds();
            if ($schoolIds !== null && !in_array($student->school_id, $schoolIds)) {
                abort(403);
            }
            
            // 1. Payment check
            if ($student->fees_status !== 'PAID') {
                return redirect()->back()->withInput()->with('error', 'Certificate cannot be generated. This student\'s payment status is: ' . ($student->fees_status ?? 'NOT PAID'));
            }
            
            // 2. Batch completion check for the specific batch selected
            $isBatchCompleted = \Illuminate\Support\Facades\DB::table('student_sembatches')
                ->where('student_id', $student->student_id)
                ->where('sembatches_id', $request->sembatches_id)
                ->where('status', 1) // 1 = Completed
                ->exists();
                
            if (!$isBatchCompleted) {
                return redirect()->back()->withInput()->with('error', 'Certificate cannot be generated. This student has not completed the selected semester/batch.');
            }
            
        } elseif ($request->type === 'EVENT') {
            $reference_id = $request->reference_id_event;

            $schoolIds = Auth::user()->restrictedSchoolIds();
            if ($schoolIds !== null && !Event::whereKey($reference_id)->whereIn('school_id', $schoolIds)->exists()) {
                abort(403);
            }
        } else {
            $reference_id = $request->sembatches_id;
        }

        // Check for existing record to overwrite
        $certificate = Certificate::where('type', $request->type)
            ->where('reference_id', $reference_id)
            ->where('sembatches_id', $request->sembatches_id)
            ->first();

        $data = [
            'type' => $request->type,
            'reference_id' => $reference_id,
            'sembatches_id' => $request->sembatches_id,
            'recipient_name' => strtoupper($request->recipient_name),
            'start_date' => $batch->start_date,
            'end_date' => $batch->end_date,
            'generated_date' => $request->generated_date,
            'created_by' => Auth::id(),
        ];

        if ($certificate) {
            $certificate->update($data);
        } else {
            // Generate New PSK Number
            $year_short = date('y', strtotime($request->generated_date));
            $count = Certificate::whereYear('generated_date', date('Y', strtotime($request->generated_date)))->count() + 1;
            $data['certificate_no'] = "PSK-{$year_short}A-" . str_pad($count, 4, '0', STR_PAD_LEFT);
            $certificate = Certificate::create($data);
        }

        return redirect()->route('certificates.index')
            ->with('success', 'Certificate ' . ($certificate->wasRecentlyCreated ? 'generated' : 'updated') . ' successfully.');
    }

    public function show(Certificate $certificate)
    {
        $this->authoriseCertificate($certificate);

        return view('certificates.preview', compact('certificate'));
    }

    public function preview($id)
    {
        $certificate = Certificate::findOrFail($id);

        $this->authoriseCertificate($certificate);

        return view('certificates.preview', compact('certificate'));
    }

    /**
     * Limit a certificate query to the schools the current user belongs to.
     *
     * Certificates point at their subject through type + reference_id rather
     * than a relation, so each type is matched against its own table. PROGRAM
     * certificates belong to no school, so a school-side user sees none.
     */
    private function applySchoolScope($query): void
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        if ($schoolIds === null) {
            return;
        }

        $query->where(function ($q) use ($schoolIds) {
            $q->where(function ($sub) use ($schoolIds) {
                $sub->where('type', 'STUDENT')
                    ->whereIn('reference_id', Student::whereIn('school_id', $schoolIds)->select('student_id'));
            })->orWhere(function ($sub) use ($schoolIds) {
                $sub->where('type', 'EVENT')
                    ->whereIn('reference_id', Event::whereIn('school_id', $schoolIds)->select('event_id'));
            });
        });
    }

    /**
     * Abort unless the certificate is one the current user is allowed to see.
     */
    private function authoriseCertificate(Certificate $certificate): void
    {
        if (Auth::user()->restrictedSchoolIds() === null) {
            return;
        }

        $query = Certificate::whereKey($certificate->getKey());
        $this->applySchoolScope($query);

        if (!$query->exists()) {
            abort(403);
        }
    }
}
