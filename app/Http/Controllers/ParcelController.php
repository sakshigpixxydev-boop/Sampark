<?php

namespace App\Http\Controllers;

use App\Models\Parcel;
use App\Models\ParcelCompany;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParcelController extends Controller
{
    /**
     * Display a listing of parcels
     */
    public function index(Request $request)
    {
        $query = Parcel::with(['student', 'parcelCompany']);

        $this->applySchoolScope($query);
        
        // Filter by student
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        
        // Filter by parcel company
        if ($request->filled('parcelcom_id')) {
            $query->where('parcelcom_id', $request->parcelcom_id);
        }
        
        // Search by tracking number
        if ($request->filled('search')) {
            $query->where('trakingnumber', 'like', '%' . $request->search . '%');
        }
        
        $parcels = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get data for filters
        $students = $this->scopedStudents();
        $parcelCompanies = ParcelCompany::active()->orderBy('companyname')->get();
        
        return view('parcels.index', compact('parcels', 'students', 'parcelCompanies'));
    }
    
    /**
     * Show the form for creating a new parcel
     */
    public function create()
    {
        $students = $this->scopedStudents();
        $parcelCompanies = ParcelCompany::active()->orderBy('companyname')->get();
        
        return view('parcels.create', compact('students', 'parcelCompanies'));
    }
    
    /**
     * Store a newly created parcel
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student,student_id',
            'parcelcom_id' => 'required|exists:parcelcompany,parcelcom_id',
            'trakingnumber' => 'required|string|max:150',
            'notes' => 'nullable|string|max:500',
        ], [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student does not exist.',
            'parcelcom_id.required' => 'Please select a parcel company.',
            'parcelcom_id.exists' => 'The selected parcel company does not exist.',
            'trakingnumber.required' => 'Tracking number is required.',
            'trakingnumber.max' => 'Tracking number cannot exceed 150 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ]);
        
        $this->authoriseStudent($validated['student_id']);

        $parcel = Parcel::create($validated);
        $this->sendStudyMaterialDispatchEmail($parcel);
        
        return redirect()->route('parcels.index')
            ->with('success', 'Parcel created successfully.');
    }
    
    /**
     * Show the form for editing a parcel
     */
    public function edit(Parcel $parcel)
    {
        $this->authoriseParcel($parcel);

        $students = $this->scopedStudents();
        $parcelCompanies = ParcelCompany::active()->orderBy('companyname')->get();
        
        return view('parcels.edit', compact('parcel', 'students', 'parcelCompanies'));
    }
    
    /**
     * Update the specified parcel
     */
    public function update(Request $request, Parcel $parcel)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student,student_id',
            'parcelcom_id' => 'required|exists:parcelcompany,parcelcom_id',
            'trakingnumber' => 'required|string|max:150',
            'notes' => 'nullable|string|max:500',
        ], [
            'student_id.required' => 'Please select a student.',
            'student_id.exists' => 'The selected student does not exist.',
            'parcelcom_id.required' => 'Please select a parcel company.',
            'parcelcom_id.exists' => 'The selected parcel company does not exist.',
            'trakingnumber.required' => 'Tracking number is required.',
            'trakingnumber.max' => 'Tracking number cannot exceed 150 characters.',
            'notes.max' => 'Notes cannot exceed 500 characters.',
        ]);
        
        $this->authoriseParcel($parcel);
        $this->authoriseStudent($validated['student_id']);

        $parcel->update($validated);
        $this->sendStudyMaterialDispatchEmail($parcel);
        
        return redirect()->route('parcels.index')
            ->with('success', 'Parcel updated successfully.');
    }
    
    /**
     * Remove the specified parcel
     */
    public function destroy(Parcel $parcel)
    {
        $this->authoriseParcel($parcel);

        $parcel->delete();
        
        return redirect()->route('parcels.index')
            ->with('success', 'Parcel deleted successfully.');
    }

    /**
     * Limit a parcel query to students of the schools the user belongs to.
     */
    private function applySchoolScope($query): void
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        if ($schoolIds === null) {
            return;
        }

        $query->whereHas('student', function ($q) use ($schoolIds) {
            $q->whereIn('school_id', $schoolIds);
        });
    }

    /**
     * Students the current user may create or edit a parcel for.
     */
    private function scopedStudents()
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        return Student::when($schoolIds !== null, fn ($q) => $q->whereIn('school_id', $schoolIds))
            ->orderBy('fname')
            ->get();
    }

    /**
     * Abort unless the parcel's student belongs to one of the user's schools.
     */
    private function authoriseParcel(Parcel $parcel): void
    {
        $this->authoriseStudent($parcel->student_id);
    }

    /**
     * Abort unless the student belongs to one of the user's schools.
     */
    private function authoriseStudent($studentId): void
    {
        $schoolIds = Auth::user()->restrictedSchoolIds();

        if ($schoolIds === null) {
            return;
        }

        $belongs = Student::whereKey($studentId)
            ->whereIn('school_id', $schoolIds)
            ->exists();

        if (!$belongs) {
            abort(403);
        }
    }

    /**
     * Send study material dispatch email using seeded mail template module
     */
    private function sendStudyMaterialDispatchEmail($parcel)
    {
        try {
            $student = $parcel->student;
            if (!$student) return;

            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            $batch = $student->semesterBatches()->first();
            $currentSemester = $batch ? ($batch->display_name ?: 'Semester ' . $batch->semester) : 'N/A';

            $parcelCompany = $parcel->parcelCompany;
            $courierPartner = $parcelCompany ? $parcelCompany->companyname : 'Courier';
            $trackingId = $parcel->trakingnumber;
            
            // Note: DB column is create_at not created_at
            $dispatchDate = $parcel->create_at ? \Carbon\Carbon::parse($parcel->create_at)->format('d/m/Y') : now()->format('d/m/Y');

            $trackingUrl = $parcelCompany ? $parcelCompany->trackingurl : '';
            if ($trackingUrl && strpos($trackingUrl, '{tracking_number}') !== false) {
                $trackingLink = str_replace('{tracking_number}', $trackingId, $trackingUrl);
            } else {
                $trackingLink = $trackingUrl ? $trackingUrl . $trackingId : '#';
            }

            $emailService = new \App\Services\ExternalEmailService();
            $rendered = \App\Helpers\MailTemplateHelper::render('study_material_dispatch', [
                'guardian_name' => $guardianName,
                'current_semester' => $currentSemester,
                'student_name' => $studentName,
                'courier_partner' => $courierPartner,
                'tracking_id' => $trackingId,
                'dispatch_date' => $dispatchDate,
                'tracking_link' => $trackingLink,
            ], $student->selected_language);

            if ($rendered) {
                $to = [$student->email => $studentName];
                $emailService->sendSmtpMail($to, $rendered['subject'], $rendered['message']);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Study Material Dispatch Email Error: ' . $e->getMessage());
        }
    }
}
