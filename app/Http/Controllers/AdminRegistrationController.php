<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Document;
use App\Models\UploadBatch;
use App\Models\PendingStudent;
use App\Models\FeePlan;
use App\Models\Taluko;
use App\Helpers\DataMaskingHelper;
use App\Services\ExternalEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AdminRegistrationController extends Controller
{
    public function index(Request $request)
    {
        // Handle clear filters
        if ($request->has('clear_filters')) {
            $status = $request->get('status', 'all');
            return redirect()->route('admin.registrations.index', ['status' => $status]);
        }

        $selectedTab = $request->get('status', 'all');

        // Create separate base queries with school restrictions applied first
        $activeBaseQuery = Student::with(['creator.role', 'school', 'taluka.district.state', 'semesterBatches.feePlans', 'document']);
        $archivedBaseQuery = Student::onlyTrashed()->with(['creator.role', 'school', 'taluka.district.state', 'semesterBatches.feePlans', 'document']);

        $user = auth()->user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (empty($schoolIds)) {
                $schoolIds = [0];
            }
            $activeBaseQuery->whereIn('school_id', $schoolIds);
            $archivedBaseQuery->whereIn('school_id', $schoolIds);
        }

        // Apply filters to both base queries
        $this->applyFilters($request, $activeBaseQuery);
        $this->applyFilters($request, $archivedBaseQuery);

        $tabCounts = [
            'all' => (clone $activeBaseQuery)->count(),
            'pending_docs' => (clone $activeBaseQuery)->where(function($q) {
                    $q->whereNotIn('application_status', ['Enrolled', 'Approved', 'Rejected', 'Documents Submitted', 'Done'])
                      ->orWhereNull('application_status');
                })
                ->where(function($q) {
                    $q->whereDoesntHave('document')
                      ->orWhereHas('document', function($dq) {
                          $dq->whereNull('photo')->orWhere('photo', '');
                      });
                })->count(),
            'processing' => (clone $activeBaseQuery)->where('fees_status', 'In Progress')->count(),
            'enrolled' => (clone $activeBaseQuery)->whereIn('application_status', ['Enrolled', 'Done'])->count(),
            'rejected' => (clone $activeBaseQuery)->where('application_status', 'Rejected')->count(),
            'failed' => (clone $activeBaseQuery)->where(function($q) {
                $q->where('fees_status', '!=', 'Paid')
                  ->orWhereNull('fees_status');
            })->whereHas('invoices.payments', function($q) {
                $q->where('status', '3');
            })->count(),
            'unpaid' => (clone $activeBaseQuery)->where(function($q) {
                    $q->whereNotIn('application_status', ['Enrolled', 'Done', 'Rejected'])
                      ->orWhereNull('application_status');
                })
                ->where(function($q) {
                    $q->whereNotIn('fees_status', ['Paid', 'In Progress'])
                      ->orWhereNull('fees_status')
                      ->orWhere('fees_status', '');
                })->count(),
            'ready_to_enroll' => (clone $activeBaseQuery)->whereIn('application_status', ['Approved', 'Documents Submitted'])
                ->where('fees_status', 'Paid')->count(),
            'archived' => (clone $archivedBaseQuery)->count(),
        ];

        // Apply active tab status filter
        if ($selectedTab === 'archived') {
            $query = $archivedBaseQuery;
        } else {
            $query = $activeBaseQuery;

            if ($selectedTab === 'pending_docs') {
                $query->where(function($q) {
                    $q->whereNotIn('application_status', ['Enrolled', 'Approved', 'Rejected', 'Documents Submitted', 'Done'])
                      ->orWhereNull('application_status');
                })
                ->where(function($q) {
                    $q->whereDoesntHave('document')
                      ->orWhereHas('document', function($dq) {
                          $dq->whereNull('photo')->orWhere('photo', '');
                      });
                });
            } elseif ($selectedTab === 'processing') {
                $query->where('fees_status', 'In Progress');
            } elseif ($selectedTab === 'enrolled') {
                $query->whereIn('application_status', ['Enrolled', 'Done']);
            } elseif ($selectedTab === 'rejected') {
                $query->where('application_status', 'Rejected');
            } elseif ($selectedTab === 'failed') {
                $query->where(function($q) {
                    $q->where('fees_status', '!=', 'Paid')
                      ->orWhereNull('fees_status');
                })->whereHas('invoices.payments', function($q) {
                    $q->where('status', '3');
                });
            } elseif ($selectedTab === 'unpaid') {
                $query->where(function($q) {
                        $q->whereNotIn('application_status', ['Enrolled', 'Done', 'Rejected'])
                          ->orWhereNull('application_status');
                    })
                    ->where(function($q) {
                        $q->whereNotIn('fees_status', ['Paid', 'In Progress'])
                          ->orWhereNull('fees_status')
                          ->orWhere('fees_status', '');
                    });
            } elseif ($selectedTab === 'ready_to_enroll') {
                $query->whereIn('application_status', ['Approved', 'Documents Submitted'])->where('fees_status', 'Paid');
            } else {
                $selectedTab = 'all';
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'student_id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['student_id', 'fname', 'lname', 'created_at', 'fees_status', 'application_status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('student_id', 'desc');
        }

        $students = $query->paginate(15)->withQueryString();

        $sources = [
            'form' => 'Public Form',
            'School' => 'School Admin',
            'Initiator' => 'Initiator',
            'Admin' => 'Admin (Manual)',
            'migrated' => 'Migrated Students'
        ];

        $statuses = [
            'pending' => 'Pending',
            'registered' => 'Registered'
        ];

        $payment_statuses = [
            'paid' => 'Paid',
            'in_progress' => 'In Progress',
            'unpaid' => 'Unpaid'
        ];

        // Load dropdown options for the filter UI
        $schools = \App\Models\School::where('status', 1)->orderBy('schoolname')->get();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (empty($schoolIds)) {
                $schoolIds = [0];
            }
            $batches = \App\Models\SemesterBatch::with('feePlans')->whereHas('feePlans', function($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds)
                  ->orWhereNull('school_id');
            })->orderBy('start_date', 'desc')->get();
        } else {
            $batches = \App\Models\SemesterBatch::with('feePlans')->orderBy('start_date', 'desc')->get();
        }
        $educationalBoards = \App\Models\EducationalBoard::orderBy('boardname')->get();
        $standards = \App\Models\Standard::orderBy('std_id')->get();
        $mediums = \DB::table('ms_medium')->orderBy('mediumname')->get();
        $mandals = \DB::table('ms_satsangmandal')->orderBy('mandalname')->get();
        $mandirs = \DB::table('ms_shikharmandir')->orderBy('mname')->get();
        $mailTemplates = \App\Models\MailTemplate::orderBy('title_en')->get();
        $subscriptions = \App\Models\Student::whereNotNull('subscription')
            ->where('subscription', '!=', '')
            ->distinct()
            ->orderBy('subscription')
            ->pluck('subscription')
            ->toArray();

        if ($request->ajax()) {
            return view('admin.registrations.partials.student_grid', compact(
                'students',
                'sources',
                'statuses',
                'payment_statuses',
                'schools',
                'batches',
                'educationalBoards',
                'standards',
                'mediums',
                'mandals',
                'mandirs',
                'tabCounts',
                'selectedTab',
                'mailTemplates',
                'subscriptions'
            ))->render();
        }

        return view('admin.registrations.index', compact(
            'students',
            'sources',
            'statuses',
            'payment_statuses',
            'schools',
            'batches',
            'educationalBoards',
            'standards',
            'mediums',
            'mandals',
            'mandirs',
            'tabCounts',
            'selectedTab',
            'mailTemplates',
            'subscriptions'
        ));
    }

    public function history(Request $request)
    {
        $query = \App\Models\StudentHistory::with(['student.standard', 'fromBatch', 'toBatch', 'executor']);

        // Search by student name, email, enrolment number
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('student', function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('enrolment_no', 'like', "%{$search}%");
            });
        }

        // Filter by From Semester Batch
        if ($request->filled('from_batch_id')) {
            $query->where('from_sembatches_id', $request->get('from_batch_id'));
        }

        // Filter by To Semester Batch
        if ($request->filled('to_batch_id')) {
            $query->where('to_sembatches_id', $request->get('to_batch_id'));
        }

        $histories = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $batches = \App\Models\SemesterBatch::with('feePlans')->orderBy('start_date', 'desc')->get();

        return view('admin.registrations.history', compact('histories', 'batches'));
    }

    public function show($id)
    {
        $student = Student::allCohorts()->with([
            'guardians',
            'semesterBatches.feePlans',
            'histories.fromBatch',
            'histories.toBatch',
            'histories.executor',
            'invoices.items',
            'invoices.payments.paymentMode'
        ])->withTrashed()->findOrFail($id);

        $user = auth()->user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized.');
            }
            $batches = \App\Models\SemesterBatch::with('feePlans')->whereHas('feePlans', function($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds)->orWhereNull('school_id');
            })->orderBy('start_date', 'desc')->get();
        } else {
            $batches = \App\Models\SemesterBatch::with('feePlans')->orderBy('start_date', 'desc')->get();
        }

        $document = Document::where('student_id', $id)->first();

        // Fetch courses from Edmingle API
        $edmingleService = new \App\Services\EdmingleService();
        $courses = $edmingleService->getCourses();

        $languagesMap = Student::getLanguagesMap();
        $educationMediumMap = Student::getEducationMediumMap();
        $learnAboutMap = Student::getLearnAboutMap();

        return view('admin.registrations.show', compact('student', 'document', 'courses', 'batches', 'languagesMap', 'educationMediumMap', 'learnAboutMap'));
    }

    public function updateBatch(Request $request, $id)
    {
        $student = Student::allCohorts()->with('invoices')->findOrFail($id);

        // Changing a student's semester batch reassigns their fee plan and
        // invoice, so it stays with master admin.
        $user = auth()->user();
        if (!$user->isMasterAdmin() && !$user->hasPermission(10001)) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'target_sembatch_id' => 'required|exists:sembatches,sembatches_id',
            'confirm_fee_mismatch' => 'nullable|boolean',
        ]);

        $targetBatch = \App\Models\SemesterBatch::with('feePlans')->findOrFail($request->target_sembatch_id);

        // 1. Resolve matching Fee Plan for target batch & school (or matching existing subscription)
        $feePlan = $targetBatch->feePlans
            ->where('fee_name', $student->subscription)
            ->first();

        if (!$feePlan && $student->school_id) {
            $feePlan = $targetBatch->feePlans
                ->where('school_id', $student->school_id)
                ->first();
        }

        if (!$feePlan) {
            $feePlan = $targetBatch->feePlans
                ->whereNull('school_id')
                ->first();
        }

        // 2. Validate Fee Plan Amount against Paid Payments / Invoices to prevent accountant discrepancies
        if ($feePlan && $student->fees_status === 'Paid') {
            $paidInvoice = $student->invoices->where('status', 'PAID')->first();
            $paidAmount = $paidInvoice ? (float)$paidInvoice->total_due : null;

            if ($paidAmount !== null && (float)$feePlan->amount != $paidAmount) {
                if (!$request->boolean('confirm_fee_mismatch')) {
                    $targetAmtFmt = number_format($feePlan->amount, 2);
                    $paidAmtFmt = number_format($paidAmount, 2);
                    return back()->with('error', "<strong>Fee Amount Mismatch Warning:</strong> The student has paid <strong>₹{$paidAmtFmt}</strong>, but the selected batch fee plan ('{$feePlan->fee_name}') is <strong>₹{$targetAmtFmt}</strong>. Changing to this batch may cause accounting discrepancies. If you are certain, check 'Confirm Fee Mismatch' and submit again.");
                }
            }
        }

        DB::beginTransaction();
        try {
            $activePivot = DB::table('student_sembatches')
                ->where('student_id', $student->student_id)
                ->where('status', 0)
                ->first();

            if ($activePivot) {
                DB::table('student_sembatches')
                    ->where('student_id', $student->student_id)
                    ->where('status', 0)
                    ->update([
                        'sembatches_id' => $targetBatch->sembatches_id,
                        'assigned_at' => now(),
                    ]);
            } else {
                DB::table('student_sembatches')->insert([
                    'student_id' => $student->student_id,
                    'sembatches_id' => $targetBatch->sembatches_id,
                    'status' => 0,
                    'assigned_at' => now(),
                ]);
            }

            $updateData = [];
            if ($targetBatch->std_id) {
                $updateData['std_id'] = $targetBatch->std_id;
            }

            // Automatically set subscription to exact fee_name from resolved FeePlan
            if ($feePlan) {
                $updateData['subscription'] = $feePlan->fee_name;
            }

            if (!empty($updateData)) {
                $student->update($updateData);
            }

            if ($targetBatch->edmingle_course_id) {
                try {
                    $edmingleService = new \App\Services\EdmingleService();
                    $portalId = $student->portal_id;
                    if (!$portalId) {
                        $portalId = $edmingleService->getStudentByEmail($student->email);
                        if (!$portalId) {
                            $portalId = $edmingleService->createStudent($student);
                        }
                        if ($portalId) {
                            $student->update(['portal_id' => $portalId]);
                        }
                    }

                    if ($portalId) {
                        $enrolledCourses = $edmingleService->getStudentEnrolledCourses($portalId);
                        if (in_array((int)$targetBatch->edmingle_course_id, $enrolledCourses)) {
                            if ($targetBatch->edmingle_batch_id) {
                                try {
                                    $edmingleService->addStudentToBatch($portalId, $targetBatch->edmingle_batch_id);
                                } catch (\Exception $e) {
                                    Log::warning("Edmingle sync warning on batch update (addStudentToBatch): " . $e->getMessage());
                                }
                            }
                        } else {
                            $edmingleService->enrollStudent($portalId, $targetBatch->edmingle_course_id, $targetBatch->edmingle_batch_id);
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Edmingle sync warning on batch update: " . $e->getMessage());
                }
            }

            DB::commit();

            $batchName = $targetBatch->display_name ?: ('Semester ' . $targetBatch->sembatches_id);
            $planMsg = $feePlan ? " Plan: '{$feePlan->fee_name}' (₹" . number_format($feePlan->amount, 2) . ")" : "";
            return back()->with('success', "Student semester batch updated to {$batchName}!{$planMsg}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update student batch: " . $e->getMessage());
            return back()->with('error', 'Failed to update student batch: ' . $e->getMessage());
        }
    }


    public function approve(Request $request, $id)
    {
        $student = Student::allCohorts()->findOrFail($id);

        $user = auth()->user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            if ($user->hasRole('Educator') && !$user->hasPermission(11605)) {
                abort(403, 'Unauthorized action.');
            }
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
            $student->update([
                'school_approval_status' => 'Approved',
                'application_status' => 'Approved',
            ]);
            return back()->with('success', 'Student approved at school level.');
        }

        // 1. Short-circuit check if already enrolled or rejected
        if ($student->application_status === 'Enrolled') {
            return back()->with('error', 'This student is already enrolled.');
        }



        if ($student->application_status === 'Rejected') {
            return back()->with('error', 'This student application has been rejected.');
        }

        $reasons = [];

        // 2. Validate prerequisites for pending approval
        if ($student->fees_status !== 'Paid') {
            $reasons[] = 'Fee payment is pending (current status: ' . ($student->fees_status ?: 'Unpaid') . ').';
        }

        // Profile photo validation check has been removed to allow enrollment without photo.
        // It will instead be requested via a post-login modal.

        // The application_status check is removed/relaxed to allow enrolling students who have not yet submitted documents (e.g. photo).
        // if ($student->application_status !== 'Documents Submitted') {
        //     $reasons[] = 'Application status is ' . ($student->application_status ?: 'Initiated') . ' (must be Documents Submitted).';
        // }

        if (!empty($reasons)) {
            if (count($reasons) === 1) {
                $message = 'Student cannot be approved at this stage: ' . $reasons[0];
            } else {
                $message = 'Student cannot be approved at this stage:<ul class="list-disc list-inside pl-1 mt-1 space-y-0.5">';
                foreach ($reasons as $reason) {
                    $message .= '<li>' . e($reason) . '</li>';
                }
                $message .= '</ul>';
            }
            return back()->with('error', $message);
        }

        // Try to get fee plan matching student's school or global
        $feePlan = \App\Models\FeePlan::where('fee_name', $student->subscription)
            ->where(function($q) use ($student) {
                $q->where('school_id', $student->school_id)
                  ->orWhereNull('school_id');
            })
            ->orderByRaw('school_id DESC')
            ->first();

        if (!$feePlan) {
            return back()->with('error', 'Fee plan not found for this student\'s subscription.');
        }

        DB::beginTransaction();
        try {
            $edmingleService = new \App\Services\EdmingleService();
            
            // Check if student already exists in Edmingle by email
            $portalId = $edmingleService->getStudentByEmail($student->email);
            
            if (!$portalId) {
                // Create student in Edmingle
                $portalId = $edmingleService->createStudent($student);
                if (!$portalId) {
                    throw new \Exception('Failed to create student on Edmingle.');
                }
            }

            $enrolledCourses = $edmingleService->getStudentEnrolledCourses($portalId);
            $hasEnrolledInAny = false;

            // Enroll student in all semester batches associated with the fee plan
            if ($feePlan->semesterBatches()->exists()) {
                foreach ($feePlan->semesterBatches as $batch) {
                    if ($batch->edmingle_course_id) {
                        $courseId = $batch->edmingle_course_id;
                        $batchId = $batch->edmingle_batch_id;

                        if (!in_array((int)$courseId, $enrolledCourses)) {
                            // Enroll student in course/batch on Edmingle
                            $enrolled = $edmingleService->enrollStudent($portalId, $courseId, $batchId);
                            if (!$enrolled) {
                                throw new \Exception("Failed to enroll student {$student->email} in Edmingle course {$courseId}");
                            }
                            $enrolledCourses[] = (int)$courseId;
                            $hasEnrolledInAny = true;
                        } else {
                            // If already enrolled in the course bundle, ensure student is added to the target batch
                            if ($batchId) {
                                try {
                                    $edmingleService->addStudentToBatch($portalId, $batchId);
                                } catch (\Exception $e) {
                                    Log::warning("Edmingle: Student {$student->email} was already enrolled in course bundle {$courseId}, but batch assignment for batch {$batchId} failed: " . $e->getMessage());
                                }
                            }
                        }
                    }

                    // Assign student to semester batch in our database
                    if (!$student->semesterBatches()->where('sembatches.sembatches_id', $batch->sembatches_id)->exists()) {
                        $student->semesterBatches()->attach($batch->sembatches_id, [
                            'status' => 0, // 0 = Active
                            'assigned_at' => now(),
                        ]);
                    }
                }
            }

            $emailPrefix = explode('@', $student->email)[0];
            $lmsUser = \Illuminate\Support\Str::slug($emailPrefix, '.');
            $lmsPass = \Illuminate\Support\Str::random(8);

            // Update student with lms user, password, and portal_id
            $student->update([
                'application_status' => 'Enrolled',
                'lmsuser' => $lmsUser,
                'lmspswd' => $lmsPass,
                'portal_id' => $portalId
            ]);

            // Create sysuser record for student login
            $studentRole = \App\Models\Role::where('rolename', 'Student')->first();
            if (!$studentRole) {
                throw new \Exception('Student role not found in system.');
            }

            // Check if sysuser already exists for this student
            $existingUser = \App\Models\SysUser::where('email', $student->email)->first();
            
            if (!$existingUser) {
                // Create new sysuser
                $sysUser = \App\Models\SysUser::create([
                    'role_id' => $studentRole->role_id,
                    'fname' => $student->fname,
                    'mname' => $student->mname,
                    'lname' => $student->lname,
                    'email' => $student->email,
                    'phone' => $student->contact_no,
                    'whatsapp' => $student->whatsapp_no,
                    'username' => $lmsUser,
                    'pswd' => $lmsPass,
                    'inactive' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Link sysuser to student
                $sysUser->students()->attach($student->student_id);
            } else {
                // Update existing sysuser
                $existingUser->update([
                    'username' => $lmsUser,
                    'pswd' => $lmsPass,
                    'inactive' => false,
                    'updated_at' => now(),
                ]);

                // Ensure linkage exists
                if (!$existingUser->students()->where('student.student_id', $student->student_id)->exists()) {
                    $existingUser->students()->attach($student->student_id);
                }
            }

            $this->sendWelcomeEmail($student);

            DB::commit();
            return redirect()->route('admin.registrations.index')->with('success', 'Student enrolled and approved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approval Error: ' . $e->getMessage());
            return back()->with('error', 'Edmingle Registration Failed: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $student = Student::allCohorts()->findOrFail($id);

        $user = auth()->user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            if ($user->hasRole('Educator') && !$user->hasPermission(11606)) {
                abort(403, 'Unauthorized action.');
            }
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
            DB::beginTransaction();
            try {
                $student->update([
                    'school_approval_status' => 'Rejected',
                    'application_status' => 'Rejected',
                    'notes' => $request->reason
                ]);

                try {
                    $this->sendRejectionEmail($student, $request->reason);
                } catch (\Exception $mailEx) {
                    Log::error('Rejection Email Error (School): ' . $mailEx->getMessage());
                }

                DB::commit();
                return redirect()->route('admin.registrations.index')->with('success', 'Student application rejected.');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('School Rejection Error: ' . $e->getMessage());
                return back()->with('error', 'Something went wrong: ' . $e->getMessage());
            }
        }

        DB::beginTransaction();
        try {
            $student->update([
                'application_status' => 'Rejected',
                'notes' => $request->reason
            ]);

            $this->sendRejectionEmail($student, $request->reason);

            DB::commit();
            return redirect()->route('admin.registrations.index')->with('success', 'Student application rejected.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rejection Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    private function applyFilters(Request $request, $query)
    {
        // 1. Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fname', 'LIKE', "%$search%")
                  ->orWhere('mname', 'LIKE', "%$search%")
                  ->orWhere('lname', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%")
                  ->orWhere('contact_no', 'LIKE', "%$search%")
                  ->orWhere('whatsapp_no', 'LIKE', "%$search%")
                  ->orWhere('enrolment_no', 'LIKE', "%$search%");
            });
        }

        // 2. Source filter
        if ($request->filled('source')) {
            $source = strtolower($request->source);
            if ($source === 'form') {
                $query->where(function($q) {
                    $q->whereNull('createdby')->orWhere('createdby', 0)->orWhere('createdby', '');
                });
            } elseif ($source === 'migrated') {
                $query->where('createdby', 'Migration_Engine');
            } else {
                $query->whereHas('creator.role', function($q) use ($source) {
                    $q->whereRaw('LOWER(rolename) = ?', [$source]);
                });
            }
        }

        // 3. Payment Status filter
        if ($request->filled('payment_status')) {
            $payStatus = strtolower($request->payment_status);
            if ($payStatus === 'paid') {
                $query->where('fees_status', 'Paid');
            } elseif ($payStatus === 'in_progress') {
                $query->where('fees_status', 'In Progress');
            } elseif ($payStatus === 'unpaid') {
                $query->where(function($q) {
                    $q->where('fees_status', '!=', 'Paid')
                      ->where('fees_status', '!=', 'In Progress')
                      ->orWhereNull('fees_status');
                });
            }
        }

        // 4. School filter
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        // 5. Batch filter
        if ($request->filled('sembatches_id')) {
            $query->whereHas('semesterBatches', function ($q) use ($request) {
                $q->where('student_sembatches.sembatches_id', $request->sembatches_id);
            });
        }

        // 6. City / Village filter
        if ($request->filled('city_village')) {
            $query->where('city_village', 'LIKE', '%' . $request->city_village . '%');
        }

        // 7. Educational Board filter
        if ($request->filled('eduboard_id')) {
            $boardIds = is_array($request->eduboard_id) ? $request->eduboard_id : [$request->eduboard_id];
            $query->whereIn('eduboard_id', $boardIds);
        }

        // 8. Standard filter
        if ($request->filled('std_id')) {
            $query->where('std_id', $request->std_id);
        }

        // 9. Stream filter
        if ($request->filled('stream')) {
            $query->where('stream', $request->stream);
        }

        // 10. Medium filter
        if ($request->filled('educationmedium')) {
            $query->where('educationmedium', $request->educationmedium);
        }

        // 11. Attending Sabha filter
        if ($request->filled('attending_sabha')) {
            if ($request->attending_sabha === 'Yes') {
                $query->where(function($q) {
                    $q->whereNotNull('satsangmandal_id')
                      ->orWhereNotNull('kshetra_id')
                      ->orWhereNotNull('shikharm_id')
                      ->orWhereNotNull('satsang_karyakar_name');
                });
            } else {
                $query->whereNull('satsangmandal_id')
                      ->whereNull('kshetra_id')
                      ->whereNull('shikharm_id')
                      ->whereNull('satsang_karyakar_name');
            }
        }

        // 12. Satsang Mandal filter
        if ($request->filled('satsangmandal_id')) {
            $query->where('satsangmandal_id', $request->satsangmandal_id);
        }

        // 13. Shikhar Mandir filter
        if ($request->filled('shikharm_id')) {
            $query->where('shikharm_id', $request->shikharm_id);
        }

        // 14. Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // 15. Start Date and End Date filters
        if ($request->filled('start_date')) {
            $startDate = \App\Helpers\DateHelper::convertDateFormat($request->start_date);
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($request->filled('end_date')) {
            $endDate = \App\Helpers\DateHelper::convertDateFormat($request->end_date);
            $query->whereDate('created_at', '<=', $endDate);
        }

        // 16. Approved Today filter
        if ($request->filled('approved_today')) {
            $query->whereIn('application_status', ['Enrolled', 'Approved'])
                  ->whereDate('updated_at', today());
        }

        // 17. Subscription / Fee Plan filter
        if ($request->filled('subscription')) {
            $query->where('subscription', $request->subscription);
        }
    }

    public function bulkShift(Request $request)
    {
        $user = auth()->user();
        if (!$user->isMasterAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'target_sembatch_id' => 'required|exists:sembatches,sembatches_id',
            'mail_template_identifier' => 'nullable|string|exists:mail_templates,identifier',
            'selected_students' => 'nullable|array',
            'excluded_students' => 'nullable|array',
            'select_all_pages' => 'nullable|string', // 'true' or 'false'
        ]);

        $targetBatch = \App\Models\SemesterBatch::findOrFail($request->target_sembatch_id);
        
        $studentIds = $this->resolveSelectedStudentIds($request);

        if ($user->hasRole('School') || $user->hasRole('Educator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            $studentIds = Student::whereIn('student_id', $studentIds)
                ->whereIn('school_id', $schoolIds)
                ->pluck('student_id')
                ->toArray();
        }

        if (empty($studentIds)) {
            return back()->with('error', 'No students selected for shift.');
        }

        $students = Student::whereIn('student_id', $studentIds)->get();
        $edmingleService = new \App\Services\EdmingleService();

        $resolvedPortalIds = [];
        $studentsToSync = [];
        $failedStudents = [];
        $successCount = 0;

        // Step 1: Ensure all students have a valid Edmingle portal ID
        foreach ($students as $student) {
            $portalId = $student->portal_id;
            if (!$portalId) {
                try {
                    // Try to search by email first
                    $portalId = $edmingleService->getStudentByEmail($student->email);
                    if (!$portalId) {
                        // Create student if they don't exist on Edmingle
                        $portalId = $edmingleService->createStudent($student);
                    }
                    if ($portalId) {
                        $student->update(['portal_id' => $portalId]);
                    }
                } catch (\Exception $e) {
                    Log::error("Edmingle portal resolve failed for student {$student->student_id}: " . $e->getMessage());
                }
            }

            if ($portalId) {
                $resolvedPortalIds[] = $portalId;
                $studentsToSync[] = $student;
            } else {
                $failedStudents[] = [
                    'id' => $student->student_id,
                    'name' => $student->fname . ' ' . $student->lname,
                    'reason' => 'Could not resolve or create student portal ID on Edmingle.'
                ];
            }
        }

        // Step 2: Perform bulk assignment on Edmingle if target batch has course/batch ID configured
        if ($targetBatch->edmingle_course_id && !empty($resolvedPortalIds)) {
            try {
                // Chunk students to prevent large payloads / gateway timeouts on Edmingle (chunk size 100)
                $chunks = array_chunk($resolvedPortalIds, 100);
                foreach ($chunks as $chunk) {
                    $edmingleService->addStudentsToBatch($chunk, $targetBatch->edmingle_batch_id);
                }
            } catch (\Exception $e) {
                Log::error("Edmingle bulk batch assignment failed: " . $e->getMessage());
                return back()->with('error', 'Failed to assign students to Edmingle batch: ' . $e->getMessage());
            }
        }

        // Step 3: Perform local database updates in a clean, unified transaction
        if (!empty($studentsToSync)) {
            DB::beginTransaction();
            try {
                $syncStudentIds = array_map(function($s) { return $s->student_id; }, $studentsToSync);

                // Fetch previous active batches before modifying the status
                $previousBatches = [];
                foreach ($studentsToSync as $student) {
                    $prev = $student->semesterBatches()->wherePivot('status', 0)->first();
                    $previousBatches[$student->student_id] = $prev;
                }

                // 1. Mark previous active batches as completed
                DB::table('student_sembatches')
                    ->whereIn('student_id', $syncStudentIds)
                    ->where('status', 0)
                    ->update([
                        'status' => 1,
                        'completed_at' => now()
                    ]);

                // 2. Fetch existing target batch mappings to avoid duplicates
                $existingPivots = DB::table('student_sembatches')
                    ->whereIn('student_id', $syncStudentIds)
                    ->where('sembatches_id', $targetBatch->sembatches_id)
                    ->pluck('student_id')
                    ->toArray();

                // Update existing mappings
                if (!empty($existingPivots)) {
                    DB::table('student_sembatches')
                        ->whereIn('student_id', $existingPivots)
                        ->where('sembatches_id', $targetBatch->sembatches_id)
                        ->update([
                            'status' => 0,
                            'assigned_at' => now(),
                            'completed_at' => null
                        ]);
                }

                // Insert new mappings
                $insertData = [];
                foreach ($syncStudentIds as $id) {
                    if (!in_array($id, $existingPivots)) {
                        $insertData[] = [
                            'student_id' => $id,
                            'sembatches_id' => $targetBatch->sembatches_id,
                            'status' => 0,
                            'assigned_at' => now()
                        ];
                    }
                }
                if (!empty($insertData)) {
                    DB::table('student_sembatches')->insert($insertData);
                }

                // 3. Update standard if target batch defines one
                if ($targetBatch->std_id) {
                    Student::whereIn('student_id', $syncStudentIds)->update(['std_id' => $targetBatch->std_id]);
                }

                // 4. Generate local sysuser accounts for newly enrolled/approved students
                $studentRole = \App\Models\Role::where('rolename', 'Student')->first();
                foreach ($studentsToSync as $student) {
                    if (!in_array($student->application_status, ['Enrolled', 'Approved'])) {
                        $emailPrefix = explode('@', $student->email)[0];
                        $lmsUser = $student->lmsuser ?: \Illuminate\Support\Str::slug($emailPrefix, '.');
                        $lmsPass = $student->lmspswd ?: \Illuminate\Support\Str::random(8);

                        $student->update([
                            'application_status' => 'Enrolled',
                            'lmsuser' => $lmsUser,
                            'lmspswd' => $lmsPass,
                        ]);

                        if ($studentRole) {
                            $existingUser = \App\Models\SysUser::where('email', $student->email)->first();
                            if (!$existingUser) {
                                $sysUser = \App\Models\SysUser::create([
                                    'role_id' => $studentRole->role_id,
                                    'fname' => $student->fname,
                                    'mname' => $student->mname,
                                    'lname' => $student->lname,
                                    'email' => $student->email,
                                    'phone' => $student->contact_no,
                                    'whatsapp' => $student->whatsapp_no,
                                    'username' => $lmsUser,
                                    'pswd' => $lmsPass,
                                    'inactive' => false,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                $sysUser->students()->attach($student->student_id);
                            } else {
                                $existingUser->update([
                                    'username' => $lmsUser,
                                    'pswd' => $lmsPass,
                                    'inactive' => false,
                                    'updated_at' => now(),
                                ]);
                                if (!$existingUser->students()->where('student.student_id', $student->student_id)->exists()) {
                                    $existingUser->students()->attach($student->student_id);
                                }
                            }
                        }
                    }
                }

                // 5. Bulk insert Student History logs
                $historyData = [];
                foreach ($studentsToSync as $student) {
                    $prevBatch = $previousBatches[$student->student_id] ?? null;
                    $prevBatchId = $prevBatch ? $prevBatch->sembatches_id : null;
                    $historyData[] = [
                        'student_id' => $student->student_id,
                        'from_sembatches_id' => $prevBatchId,
                        'to_sembatches_id' => $targetBatch->sembatches_id,
                        'status' => 'Migrated',
                        'migrated_by' => auth()->user() ? auth()->user()->sysuser_id : null,
                        'remarks' => "Bulk migrated to " . ($targetBatch->display_name ?: 'Semester ' . $targetBatch->sembatches_id),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                if (!empty($historyData)) {
                    DB::table('student_history')->insert($historyData);
                }

                // 6. Dispatch email notification jobs to run asynchronously in background
                $totalStudents = count($studentsToSync);
                foreach ($studentsToSync as $index => $student) {
                    $prevBatch = $previousBatches[$student->student_id] ?? null;
                    $prevBatchName = $prevBatch ? ($prevBatch->display_name ?: 'Semester ' . $prevBatch->sembatches_id) : 'None';
                    $newBatchName = $targetBatch->display_name ?: 'Semester ' . $targetBatch->sembatches_id;

                    $isFirst = ($index === 0);
                    $isLast = ($index === $totalStudents - 1);

                    dispatch(new \App\Jobs\SendBatchShiftEmailJob(
                        $student->student_id, 
                        $prevBatchName, 
                        $newBatchName, 
                        $request->input('mail_template_identifier'),
                        $targetBatch->sembatches_id,
                        $isFirst,
                        $isLast
                    ));
                }

                DB::commit();
                $successCount = count($studentsToSync);

                if ($successCount > 0) {
                    $newBatchName = $targetBatch->display_name ?: 'Semester ' . $targetBatch->sembatches_id;
                    $adminEmail = auth()->user() ? auth()->user()->email : config('mail.from.address');
                    dispatch(new \App\Jobs\SendBulkShiftCompleteEmailJob($successCount, $newBatchName, $adminEmail));
                }
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Bulk Shift Database update failed: " . $e->getMessage());
                return back()->with('error', 'Database shift transaction failed: ' . $e->getMessage());
            }
        }

        $message = "Successfully shifted {$successCount} student(s) to " . ($targetBatch->display_name ?: 'Semester ' . $targetBatch->sembatches_id) . ".";
        if (!empty($failedStudents)) {
            $message .= " Failed to shift " . count($failedStudents) . " student(s) due to Edmingle API error.";
        }

        return back()->with('success', $message);
    }

    public function bulkEmail(Request $request)
    {
        $user = auth()->user();
        if (!$user->isMasterAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'mail_template_identifier' => 'required|string|exists:mail_templates,identifier',
            'target_sembatch_id' => 'nullable|exists:sembatches,sembatches_id',
            'selected_students' => 'nullable|array',
            'excluded_students' => 'nullable|array',
            'select_all_pages' => 'nullable|string', // 'true' or 'false'
        ]);

        $studentIds = $this->resolveSelectedStudentIds($request);

        if ($user->hasRole('School') || $user->hasRole('Educator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            $studentIds = Student::whereIn('student_id', $studentIds)
                ->whereIn('school_id', $schoolIds)
                ->pluck('student_id')
                ->toArray();
        }

        if (empty($studentIds)) {
            return back()->with('error', 'No students selected for email.');
        }

        $templateIdentifier = $request->input('mail_template_identifier');
        $targetBatchId = $request->input('target_sembatch_id');

        // Dispatch SendBulkEmailJob for each selected student
        foreach ($studentIds as $id) {
            dispatch(new \App\Jobs\SendBulkEmailJob($id, $templateIdentifier, $targetBatchId));
        }

        return back()->with('success', 'Successfully queued emails for ' . count($studentIds) . ' student(s).');
    }

    public function bulkSyncPayments(Request $request)
    {
        $user = auth()->user();
        if (!$user->canRunRegistrationOps()) {
            abort(403, 'Unauthorized action.');
        }

        $studentIds = $this->resolveSelectedStudentIds($request);

        if ($user->hasRole('School') || $user->hasRole('Educator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            $studentIds = Student::whereIn('student_id', $studentIds)
                ->whereIn('school_id', $schoolIds)
                ->pluck('student_id')
                ->toArray();
        }

        if (empty($studentIds)) {
            return back()->with('error', 'No students selected for payment sync.');
        }

        $keyId = config('razorpay.key_id') ?: config('services.razorpay.key');
        $keySecret = config('razorpay.key_secret') ?: config('services.razorpay.secret');

        if (empty($keyId) || empty($keySecret)) {
            return back()->with('error', 'Razorpay API credentials are not configured.');
        }

        $razorpayService = app(\App\Services\RazorpayService::class);
        $successCount = 0;
        $failedCount = 0;

        $students = Student::whereIn('student_id', $studentIds)
            ->where('fees_status', '!=', 'Paid')
            ->get();

        foreach ($students as $student) {
            try {
                $orderId = $student->razorpay_order_id;
                $receipt = 'order_rcptid_' . $student->student_id;

                if (empty($orderId)) {
                    // Try to resolve the order by receipt format
                    $receiptResponse = $razorpayService->getOrdersByReceipt($receipt);

                    if ($receiptResponse['success'] && isset($receiptResponse['orders']) && count($receiptResponse['orders']->items) > 0) {
                        $order = null;
                        foreach ($receiptResponse['orders']->items as $item) {
                            if ($item->status === 'paid') {
                                $order = $item;
                                break;
                            }
                        }
                        if (!$order) {
                            $order = $receiptResponse['orders']->items[0];
                        }
                        $orderId = $order->id;
                        $student->razorpay_order_id = $orderId;
                        $student->save();
                    } else {
                        // Fallback to checking by payment ID
                        $syncResult = $this->syncViaPaymentId($student, $razorpayService);
                        if ($syncResult['success']) {
                            $successCount++;
                        } else {
                            $failedCount++;
                        }
                        continue;
                    }
                }

                // If we have an order ID, fetch payments and update status
                $paymentsResponse = $razorpayService->getPaymentsForOrder($orderId);
                if ($paymentsResponse['success'] && isset($paymentsResponse['payments'])) {
                    $hasPaid = false;
                    foreach ($paymentsResponse['payments']->items as $payment) {
                        if ($payment->status === 'captured') {
                            $hasPaid = true;
                            break;
                        }
                    }

                    if ($hasPaid) {
                        $student->fees_status = 'Paid';
                        $student->application_status = 'Approved';
                        $student->save();
                        $successCount++;
                    } else {
                        $failedCount++;
                    }
                } else {
                    $failedCount++;
                }

            } catch (\Exception $e) {
                Log::error("Bulk Sync Payment failed for student {$student->student_id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        $message = "Payment sync completed. Successfully synced {$successCount} student(s).";
        if ($failedCount > 0) {
            $message .= " Failed to sync {$failedCount} student(s) (no paid order/payment found).";
        }

        return back()->with('success', $message);
    }

    private function resolveSelectedStudentIds(Request $request)
    {
        $studentIds = [];
        if ($request->get('select_all_pages') === 'true') {
            $query = Student::query();
            
            
            // Apply role-based school filtering (if user is school admin or educator)
            $user = auth()->user();
            if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
                $schoolIds = $user->getAssignedSchoolIds();
                if (empty($schoolIds)) {
                    $schoolIds = [0];
                }
                $query->whereIn('school_id', $schoolIds);
            }
            
            $this->applyFilters($request, $query);

            // Apply tab status filter if present
            $selectedTab = $request->get('status', 'all');
            if ($selectedTab === 'pending_docs') {
                $query->where(function($q) {
                    $q->whereNotIn('application_status', ['Enrolled', 'Approved', 'Rejected', 'Documents Submitted', 'Done'])
                      ->orWhereNull('application_status');
                })
                ->where(function($q) {
                    $q->whereDoesntHave('document')
                      ->orWhereHas('document', function($dq) {
                          $dq->whereNull('photo')->orWhere('photo', '');
                      });
                });
            } elseif ($selectedTab === 'pending') {
                $query->where('application_status', 'Documents Submitted');
            } elseif ($selectedTab === 'processing') {
                $query->where('fees_status', 'In Progress');
            } elseif ($selectedTab === 'enrolled') {
                $query->whereIn('application_status', ['Enrolled', 'Approved', 'Done']);
            } elseif ($selectedTab === 'rejected') {
                $query->where('application_status', 'Rejected');
            } elseif ($selectedTab === 'failed') {
                $query->where(function($q) {
                    $q->where('fees_status', '!=', 'Paid')
                      ->orWhereNull('fees_status');
                })->whereHas('invoices.payments', function($q) {
                    $q->where('status', '3');
                });
            }

            if ($request->filled('excluded_students')) {
                $query->whereNotIn('student_id', $request->excluded_students);
            }
            $studentIds = $query->pluck('student_id')->toArray();
        } else {
            $studentIds = $request->input('selected_students', []);
        }

        return $studentIds;
    }

    private function sendBatchShiftEmail(Student $student, $previousSemesterName, $newSemesterName)
    {
        try {
            $emailService = new ExternalEmailService();
            
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            $variables = [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'previous_semester' => $previousSemesterName,
                'new_semester' => $newSemesterName,
                'migration_date' => now()->format('d/m/Y'),
                'enrolment_no' => $student->enrolment_no ?: 'N/A',
            ];

            $emailData = \App\Helpers\MailTemplateHelper::render('student_batch_shift', $variables);

            $subject = $emailData ? $emailData['subject'] : 'Student Semester Batch Shifted – Pramukh SANKALP';
            $message = $emailData ? $emailData['message'] : '';

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $subject, $message);
            Log::info("Migration email sent to student {$student->student_id} ({$student->email})");
        } catch (\Exception $e) {
            Log::error('Batch Shift Email Error: ' . $e->getMessage());
        }
    }

    private function sendWelcomeEmail($student)
    {
        try {
            $emailService = new ExternalEmailService();
            
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            $courseValidity = 'N/A';
            $latestBatch = $student->semesterBatches()->orderBy('end_date', 'desc')->first();
            if ($latestBatch && $latestBatch->end_date) {
                $courseValidity = \Carbon\Carbon::parse($latestBatch->end_date)->format('d/m/Y');
            } else {
                $feePlan = \App\Models\FeePlan::where('fee_name', $student->subscription)
                    ->where(function($q) use ($student) {
                        $q->where('school_id', $student->school_id)
                          ->orWhereNull('school_id');
                    })
                    ->orderByRaw('school_id DESC')
                    ->first();
                if ($feePlan && $feePlan->valid_to) {
                    $courseValidity = \Carbon\Carbon::parse($feePlan->valid_to)->format('d/m/Y');
                } else {
                    $courseValidity = now()->addYear()->format('d/m/Y');
                }
            }

            $variables = [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'portal_link' => route('login'),
                'enrolment_no' => $student->enrolment_no,
                'lmspswd' => $student->lmspswd,
                'course_validity' => $courseValidity,
            ];

            $emailData = \App\Helpers\MailTemplateHelper::render('student_welcome', $variables, $student->selected_language);

            $subject = $emailData ? $emailData['subject'] : 'Welcome to Pramukh SANKALP – Portal Access Details';
            $message = $emailData ? $emailData['message'] : '';

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Welcome Email Error: ' . $e->getMessage());
        }
    }

    private function sendPaymentSuccessEmail($student)
    {
        try {
            $emailService = new ExternalEmailService();
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            $rendered = \App\Helpers\MailTemplateHelper::render('payment_success', [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'subscription' => $student->subscription,
            ], $student->selected_language);

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $rendered['subject'], $rendered['message']);
            Log::info("Admin Sync: Payment Success email sent to {$student->email}");
        } catch (\Exception $e) {
            Log::error('Admin Sync: Payment Success Email Error: ' . $e->getMessage());
        }
    }

    private function syncViaPaymentId($student, $razorpayService, $paymentId = null)
    {
        if (empty($paymentId)) {
            $paymentId = request('payment_id') ?: request('razorpay_payment_id') ?: $student->razorpay_payment_id;
        }

        if (empty($paymentId)) {
            // Check invoice payment logs
            $latestPaymentLog = \DB::table('paymentlog')
                ->whereIn('invoice_id', \DB::table('invoice')->where('student_id', $student->student_id)->pluck('invoice_id'))
                ->where('txnid', 'LIKE', 'pay_%')
                ->orderBy('processdatetime', 'desc')
                ->first();
            if ($latestPaymentLog) {
                $paymentId = $latestPaymentLog->txnid;
            }
        }

        if (empty($paymentId)) {
            // Fallback: scan recent 100 payments on Razorpay matching by student email or student ID
            $paymentsRes = $razorpayService->getPayments(['count' => 100]);
            if ($paymentsRes['success'] && isset($paymentsRes['payments'])) {
                $items = $paymentsRes['payments']->items ?? $paymentsRes['payments'];
                foreach ($items as $item) {
                    $itemEmail = is_array($item) ? ($item['email'] ?? '') : ($item->email ?? '');
                    $itemNotes = is_array($item) ? ($item['notes'] ?? []) : ($item->notes ?? []);
                    if (is_object($itemNotes)) {
                        $itemNotes = (array)$itemNotes;
                    }
                    $studentIdFromNotes = $itemNotes['student_id'] ?? null;

                    if (
                        (!empty($studentIdFromNotes) && (int)$studentIdFromNotes === (int)$student->student_id) ||
                        (!empty($itemEmail) && strtolower($itemEmail) === strtolower($student->email))
                    ) {
                        $paymentId = is_array($item) ? ($item['id'] ?? null) : ($item->id ?? null);
                        if ($paymentId) {
                            break;
                        }
                    }
                }
            }
        }

        if (!empty($paymentId)) {
            $paymentResponse = $razorpayService->getPaymentDetails($paymentId);
            if ($paymentResponse['success']) {
                $payment = $paymentResponse['payment'];
                $status = $payment['status'] ?? '';

                if ($status === 'captured') {
                    $itemNotes = is_array($payment) ? ($payment['notes'] ?? []) : ($payment->notes ?? []);
                    if (is_object($itemNotes)) {
                        $itemNotes = (array)$itemNotes;
                    }
                    $planId = $itemNotes['plan_id'] ?? null;
                    $planName = null;
                    if ($planId) {
                        $plan = \App\Models\FeePlan::find($planId);
                        if ($plan) {
                            $planName = $plan->fee_name;
                        }
                    }

                    DB::transaction(function () use ($student, $payment, $planName) {
                        $student->fees_status = 'Paid';
                        $student->razorpay_payment_id = $payment['id'];
                        if ($planName) {
                            $student->subscription = $planName;
                        }
                        if (isset($payment['order_id']) && !empty($payment['order_id'])) {
                            $student->razorpay_order_id = $payment['order_id'];
                        }
                        if (empty($student->enrolment_no)) {
                            $student->enrolment_no = Student::generateNextEnrolmentNumber($student->student_id);
                        }
                        $student->save();
                    });

                    // Generate Invoice and Payment Log
                    try {
                        $invoiceService = new \App\Services\InvoiceService();
                        $invoiceService->generateRegistrationInvoiceAndPayment($student, $payment['id'], $payment);
                    } catch (\Exception $e) {
                        Log::error('Admin Sync Invoice Generation Error: ' . $e->getMessage());
                    }

                    $this->sendPaymentSuccessEmail($student);

                    return [
                        'success' => true,
                        'message' => 'Payment status synced successfully! Student is now marked as Paid.',
                        'payment' => $payment
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Payment status on Razorpay is: ' . strtoupper($status)
                    ];
                }
            }
        }

        return [
            'success' => false,
            'message' => 'No Razorpay payment log found or fetch failed.'
        ];
    }

    private function sendRejectionEmail($student, $reason)
    {
        try {
            $emailService = new ExternalEmailService();
            
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            $invoice = $student->invoices()->orderBy('created_at', 'desc')->first();
            $paymentLog = $invoice ? $invoice->payments()->orderBy('processdatetime', 'desc')->first() : null;
            
            $paymentAmount = $paymentLog && $paymentLog->amount ? '₹' . $paymentLog->amount : 'N/A';
            $transactionId = $paymentLog && $paymentLog->txnid ? $paymentLog->txnid : 'N/A';
            $refundStatus = $paymentLog && $paymentLog->status == '2' ? 'Refunded' : 'Pending';

            $variables = [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'reason' => $reason,
                'payment_amount' => $paymentAmount,
                'transaction_id' => $transactionId,
                'refund_status' => $refundStatus,
            ];

            $emailData = \App\Helpers\MailTemplateHelper::render('student_rejection', $variables, $student->selected_language);

            $subject = $emailData ? $emailData['subject'] : 'SANKALP Programme Registration Update';
            $message = $emailData ? $emailData['message'] : '';

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $subject, $message);
        } catch (\Exception $e) {
            Log::error('Rejection Email Error: ' . $e->getMessage());
        }
    }

    public function getBatches($bundleId)
    {
        try {
            $edmingleService = new \App\Services\EdmingleService();
            $batches = $edmingleService->getBatches($bundleId);
            return response()->json([
                'success' => true,
                'batches' => $batches
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching batches for bundle ' . $bundleId . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'batches' => [],
                'message' => $e->getMessage()
            ]);
        }
    }

    public function sendReminder($id)
    {
        $student = Student::allCohorts()->findOrFail($id);

        // Payment-side actions are admin/accountant only; school-side roles
        // approve or reject a registration and nothing more.
        if (!auth()->user()->canRunRegistrationOps()) {
            abort(403, 'Unauthorized action.');
        }

        if ($student->fees_status === 'Paid') {
            return back()->with('error', 'This student has already paid their fees.');
        }

        // Find correct plan matching student's school or global
        $plan = \App\Models\FeePlan::where('fee_name', $student->subscription)
            ->where(function($q) use ($student) {
                $q->where('school_id', $student->school_id)
                  ->orWhereNull('school_id');
            })
            ->orderByRaw('school_id DESC')
            ->first();
        if (!$plan) {
            // fallback to any active fee plan matching student's school or first active plan
            $plan = \App\Models\FeePlan::where('is_active_on_form', 1)
                ->where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })
                ->first();
        }

        if ($plan) {
            $paymentLink = route('registration.payment', [
                'id' => base64_encode($student->student_id),
                'plan_id' => $plan->fee_plan_id
            ]);
        } else {
            // Fallback to plan selection
            $paymentLink = route('registration.plans', ['id' => base64_encode($student->student_id)]);
        }

        $guardian = $student->guardians()->first();
        $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
        $studentName = $student->fname . ' ' . $student->lname;

        try {
            $emailService = new ExternalEmailService();
            
            $variables = [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'payment_link' => $paymentLink,
                'subscription' => $student->subscription ?: ($plan ? $plan->fee_name : 'Sankalp Fee Plan')
            ];

            $emailData = \App\Helpers\MailTemplateHelper::render('payment_reminder', $variables, $student->selected_language);

            $subject = $emailData ? $emailData['subject'] : 'Reminder: Complete Your SANKALP Registration';
            $message = $emailData ? $emailData['message'] : '';

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $subject, $message);

            return back()->with('success', 'Payment reminder email sent successfully to ' . $student->email);
        } catch (\Exception $e) {
            Log::error('Send Reminder Email Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send reminder email: ' . $e->getMessage());
        }
    }
    /**
     * Sync payment status for a single student from Razorpay
     */
    public function syncPayment($id)
    {
        $student = Student::allCohorts()->findOrFail($id);

        // Payment-side actions are admin/accountant only; school-side roles
        // approve or reject a registration and nothing more.
        if (!auth()->user()->canRunRegistrationOps()) {
            abort(403, 'Unauthorized action.');
        }

        if ($student->fees_status === 'Paid') {
            return back()->with('error', 'This student has already paid their fees.');
        }

        $keyId = config('razorpay.key_id') ?: config('services.razorpay.key');
        $keySecret = config('razorpay.key_secret') ?: config('services.razorpay.secret');

        if (empty($keyId) || empty($keySecret)) {
            return back()->with('error', 'Razorpay API credentials (Key ID or Key Secret) are not configured. Please set them in your environment settings.');
        }

        try {
            $razorpayService = app(\App\Services\RazorpayService::class);
            $orderId = $student->razorpay_order_id;
            $receipt = 'order_rcptid_' . $student->student_id;

            if (empty($orderId)) {
                // Try to resolve the order by receipt format
                $receiptResponse = $razorpayService->getOrdersByReceipt($receipt);

                if ($receiptResponse['success'] && isset($receiptResponse['orders']) && count($receiptResponse['orders']->items) > 0) {
                    $order = null;
                    foreach ($receiptResponse['orders']->items as $item) {
                        if ($item->status === 'paid') {
                            $order = $item;
                            break;
                        }
                    }
                    if (!$order) {
                        $order = $receiptResponse['orders']->items[0];
                    }
                    $orderId = $order->id;
                    $student->razorpay_order_id = $orderId;
                    $student->save();
                } else {
                    // Fallback to checking by payment ID
                    $syncResult = $this->syncViaPaymentId($student, $razorpayService);
                    if ($syncResult['success']) {
                        return back()->with('success', $syncResult['message']);
                    }
                    return back()->with('error', 'No Razorpay order or payment found for this student. Receipt ID tried: ' . $receipt);
                }
            }

            $orderResponse = $razorpayService->getOrderDetails($orderId);

            if (!$orderResponse['success']) {
                return back()->with('error', 'Failed to fetch order details from Razorpay: ' . $orderResponse['error']);
            }

            $order = $orderResponse['order'];

            // Fetch payments for this specific order
            $payments = null;
            try {
                $payments = $order->payments();
            } catch (\Exception $e) {
                Log::error('Razorpay sync payments fetch error: ' . $e->getMessage());
            }

            $capturedPayment = null;
            $hasFailedPayment = false;
            if ($payments && isset($payments->items)) {
                foreach ($payments->items as $paymentItem) {
                    $pStatus = is_array($paymentItem) ? ($paymentItem['status'] ?? '') : ($paymentItem->status ?? '');
                    if ($pStatus === 'captured') {
                        $capturedPayment = $paymentItem;
                        break;
                    } elseif ($pStatus === 'failed') {
                        $hasFailedPayment = true;
                    }
                }
            }

            // Fallback: If order is marked paid but no captured payment was found in the iteration, take the first payment if any
            if ($order['status'] === 'paid' && !$capturedPayment && $payments && isset($payments->items) && count($payments->items) > 0) {
                $capturedPayment = $payments->items[0];
            }

            if ($capturedPayment) {
                $paymentId = is_array($capturedPayment) ? ($capturedPayment['id'] ?? null) : ($capturedPayment->id ?? null);
                
                $paymentData = null;
                if ($paymentId) {
                    $pDetailsRes = $razorpayService->getPaymentDetails($paymentId);
                    if ($pDetailsRes['success']) {
                        $paymentData = $pDetailsRes['payment'];
                    }
                }

                $planId = null;
                if (isset($order->notes) && isset($order->notes->plan_id)) {
                    $planId = $order->notes->plan_id;
                } elseif (isset($order['notes']) && isset($order['notes']['plan_id'])) {
                    $planId = $order['notes']['plan_id'];
                }

                if (!$planId && $paymentData) {
                    $itemNotes = is_array($paymentData) ? ($paymentData['notes'] ?? []) : ($paymentData->notes ?? []);
                    if (is_object($itemNotes)) {
                        $itemNotes = (array)$itemNotes;
                    }
                    $planId = $itemNotes['plan_id'] ?? null;
                }

                $planName = null;
                if ($planId) {
                    $plan = \App\Models\FeePlan::find($planId);
                    if ($plan) {
                        $planName = $plan->fee_name;
                    }
                }

                DB::transaction(function () use ($student, $orderId, $paymentId, $planName) {
                    $student->fees_status = 'Paid';
                    $student->razorpay_payment_id = $paymentId;
                    $student->razorpay_order_id = $orderId;
                    if ($planName) {
                        $student->subscription = $planName;
                    }
                    if (empty($student->enrolment_no)) {
                        $student->enrolment_no = Student::generateNextEnrolmentNumber($student->student_id);
                    }
                    $student->save();
                });

                // Generate Invoice and Payment Log
                try {
                    $invoiceService = new \App\Services\InvoiceService();
                    $invoiceService->generateRegistrationInvoiceAndPayment($student, $paymentId, $paymentData);
                } catch (\Exception $e) {
                    Log::error('Admin Sync Invoice Generation Error: ' . $e->getMessage());
                }

                $this->sendPaymentSuccessEmail($student);

                return back()->with('success', 'Payment status synced successfully! Student is now marked as Paid.');
            }

            // Fallback to checking by payment ID (logs/scanning fallback) in case order is not 'paid' but payment went through separately
            $syncResult = $this->syncViaPaymentId($student, $razorpayService);
            if ($syncResult['success']) {
                return back()->with('success', $syncResult['message']);
            }

            // If we found failed payments and no captured ones
            if ($hasFailedPayment) {
                return back()->with('info', 'Payment status on Razorpay is: FAILED (All attempts failed)');
            }

            return back()->with('info', 'Payment status on Razorpay is still: ' . strtoupper($order['status']));

        } catch (\Exception $e) {
            Log::error('Razorpay sync error: ' . $e->getMessage());
            return back()->with('error', 'Error syncing payment: ' . $e->getMessage());
        }
    }

    /**
     * Sync all "In Progress" & "Pending" payments in bulk
     */
    public function syncAllInProgress()
    {
        $user = auth()->user();
        if (!$user->canRunRegistrationOps()) {
            abort(403, 'Unauthorized action.');
        }

        $query = Student::where('fees_status', '!=', 'Paid')
            ->where(function($q) {
                $q->whereNotNull('razorpay_order_id')
                  ->orWhere('fees_status', 'In Progress');
            });

        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (empty($schoolIds)) {
                $schoolIds = [0];
            }
            $query->whereIn('school_id', $schoolIds);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            return back()->with('info', 'No unpaid/in-progress payments found to sync.');
        }

        $paidCount = 0;
        $pendingCount = 0;
        $failedCount = 0;

        $keyId = config('razorpay.key_id') ?: config('services.razorpay.key');
        $keySecret = config('razorpay.key_secret') ?: config('services.razorpay.secret');

        if (empty($keyId) || empty($keySecret)) {
            return back()->with('error', 'Razorpay API credentials (Key ID or Key Secret) are not configured. Please set them in your environment settings.');
        }

        $razorpayService = app(\App\Services\RazorpayService::class);

        foreach ($students as $student) {
            try {
                $orderId = $student->razorpay_order_id;

                if (empty($orderId)) {
                    $receipt = 'order_rcptid_' . $student->student_id;
                    $receiptResponse = $razorpayService->getOrdersByReceipt($receipt);
                    
                    if ($receiptResponse['success'] && isset($receiptResponse['orders']) && count($receiptResponse['orders']->items) > 0) {
                        $order = null;
                        foreach ($receiptResponse['orders']->items as $item) {
                            if ($item->status === 'paid') {
                                $order = $item;
                                break;
                            }
                        }
                        if (!$order) {
                            $order = $receiptResponse['orders']->items[0];
                        }
                        $orderId = $order->id;
                        $student->razorpay_order_id = $orderId;
                        $student->save();
                    } else {
                        // Fallback to checking by payment ID
                        $syncResult = $this->syncViaPaymentId($student, $razorpayService);
                        if ($syncResult['success']) {
                            $paidCount++;
                        } else {
                            $pendingCount++;
                        }
                        continue;
                    }
                }

                $orderResponse = $razorpayService->getOrderDetails($orderId);
                
                if (!$orderResponse['success']) {
                    // Fallback to checking by payment ID even if order details API failed
                    $syncResult = $this->syncViaPaymentId($student, $razorpayService);
                    if ($syncResult['success']) {
                        $paidCount++;
                    } else {
                        $failedCount++;
                    }
                    continue;
                }

                $order = $orderResponse['order'];

                // Fetch payments for this specific order
                $payments = null;
                try {
                    $payments = $order->payments();
                } catch (\Exception $e) {
                    Log::error("Razorpay bulk sync payments fetch error for student {$student->student_id}: " . $e->getMessage());
                }

                $capturedPayment = null;
                if ($payments && isset($payments->items)) {
                    foreach ($payments->items as $paymentItem) {
                        $pStatus = is_array($paymentItem) ? ($paymentItem['status'] ?? '') : ($paymentItem->status ?? '');
                        if ($pStatus === 'captured') {
                            $capturedPayment = $paymentItem;
                            break;
                        }
                    }
                }

                // Fallback: If order is marked paid but no captured payment was found in the iteration, take the first payment if any
                if ($order['status'] === 'paid' && !$capturedPayment && $payments && isset($payments->items) && count($payments->items) > 0) {
                    $capturedPayment = $payments->items[0];
                }

                if ($capturedPayment) {
                    $paymentId = is_array($capturedPayment) ? ($capturedPayment['id'] ?? null) : ($capturedPayment->id ?? null);
                    
                    $paymentData = null;
                    if ($paymentId) {
                        $pDetailsRes = $razorpayService->getPaymentDetails($paymentId);
                        if ($pDetailsRes['success']) {
                            $paymentData = $pDetailsRes['payment'];
                        }
                    }

                    DB::transaction(function () use ($student, $orderId, $paymentId) {
                        $student->fees_status = 'Paid';
                        $student->razorpay_payment_id = $paymentId;
                        $student->razorpay_order_id = $orderId;
                        if (empty($student->enrolment_no)) {
                            $student->enrolment_no = Student::generateNextEnrolmentNumber($student->student_id);
                        }
                        $student->save();
                    });

                    // Generate Invoice and Payment Log
                    try {
                        $invoiceService = new \App\Services\InvoiceService();
                        $invoiceService->generateRegistrationInvoiceAndPayment($student, $paymentId, $paymentData);
                    } catch (\Exception $e) {
                        Log::error("Admin Bulk Sync Invoice Generation Error for student {$student->student_id}: " . $e->getMessage());
                    }

                    $this->sendPaymentSuccessEmail($student);
                    $paidCount++;
                } else {
                    // Fallback to checking by payment ID
                    $syncResult = $this->syncViaPaymentId($student, $razorpayService);
                    if ($syncResult['success']) {
                        $paidCount++;
                    } else {
                        $pendingCount++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Razorpay bulk sync error for student {$student->student_id}: " . $e->getMessage());
                $failedCount++;
            }
        }

        return back()->with('success', "Bulk sync completed. Statuses updated: {$paidCount} marked as Paid, {$pendingCount} still pending/in-progress, {$failedCount} failed to check.");
    }

    public function showPhoto($id)
    {
        $document = Document::where('student_id', $id)->first();
        if (!$document || !$document->photo) {
            abort(404);
        }

        $relativePath = str_replace('storage/', '', $document->photo);
        $fullPath = storage_path('app/public/' . $relativePath);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }

    public function destroy(Request $request, $id)
    {
        $student = Student::allCohorts()->findOrFail($id);

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $student->deleted_reason = $request->input('reason');
            $student->save();

            // Delete associated sysuser if the student is already enrolled
            if (in_array($student->application_status, ['Approved', 'Enrolled'])) {
                $sysUsers = $student->users()->get();
                foreach ($sysUsers as $sysUser) {
                    $sysUser->delete();
                }
            }

            $student->delete();

            DB::commit();
            return back()->with('success', 'Student deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete Student Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete student: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        $student = Student::allCohorts()->onlyTrashed()->findOrFail($id);

        DB::beginTransaction();
        try {
            $student->deleted_reason = null;
            $student->save();
            $student->restore();

            DB::commit();
            return back()->with('success', 'Student restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Restore Student Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to restore student: ' . $e->getMessage());
        }
    }

    public function publishBatch(Request $request)
    {
        $user = auth()->user();
        if (!$user->hasRole('School') && !$user->hasRole('Educator')) {
            return back()->with('error', 'Only school administrators or educators can publish a batch.');
        }

        if ($user->hasRole('Educator') && !$user->hasPermission(11602)) {
            return back()->with('error', 'You do not have permission to publish a batch.');
        }

        $schoolIds = $user->getAssignedSchoolIds();
        if (empty($schoolIds)) {
            return back()->with('error', 'You are not assigned to any school.');
        }
        $schoolId = $schoolIds[0];

        // 1. Check if there are any pending registrations
        $pendingCount = Student::where('school_id', $schoolId)
            ->where('registered_via_school_link', true)
            ->where('school_approval_status', 'Pending')
            ->count();

        if ($pendingCount > 0) {
            return back()->with('error', 'You have ' . $pendingCount . ' registrations pending review. Please approve or reject all of them before publishing a batch.');
        }

        // 2. Get all approved students that are not yet in a batch
        $approvedStudents = Student::where('school_id', $schoolId)
            ->where('registered_via_school_link', true)
            ->where('school_approval_status', 'Approved')
            ->whereNull('batch_id')
            ->get();

        if ($approvedStudents->isEmpty()) {
            return back()->with('error', 'There are no approved students to publish.');
        }

        $request->validate([
            'sembatches_id' => 'required|exists:sembatches,sembatches_id',
            'payment_type' => 'required|in:online,offline',
            'offline_payment_method' => 'required_if:payment_type,offline|nullable|in:bank_transfer,cheque,other',
        ]);

        $sembatches_id = $request->sembatches_id;
        $paymentType = $request->payment_type;

        // 3. Calculate total fees for the batch
        $totalFees = 0;
        $studentCount = $approvedStudents->count();
        $studentsData = [];

        foreach ($approvedStudents as $student) {
            // Find fee plan matching the student subscription and the sembatch, restricted to school or global
            $feePlan = FeePlan::where(function($q) use ($sembatches_id) {
                    $q->where('sembatches_id', $sembatches_id)
                      ->orWhereHas('semesterBatches', function ($q2) use ($sembatches_id) {
                          $q2->where('fee_plan_sembatches.sembatches_id', $sembatches_id);
                      });
                })
                ->where('fee_name', $student->subscription)
                ->where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })
                ->orderByRaw('school_id DESC')
                ->first();
            
            if (!$feePlan && $student->school_id) {
                // Try matching by student school
                $feePlan = FeePlan::where(function($q) use ($sembatches_id) {
                        $q->where('sembatches_id', $sembatches_id)
                          ->orWhereHas('semesterBatches', function ($q2) use ($sembatches_id) {
                              $q2->where('fee_plan_sembatches.sembatches_id', $sembatches_id);
                          });
                    })
                    ->where('school_id', $student->school_id)
                    ->first();
            }

            // Fallback: get any school-specific or global fee plan for this sembatch
            if (!$feePlan) {
                $feePlan = FeePlan::where(function($q) use ($sembatches_id) {
                    $q->where('sembatches_id', $sembatches_id)
                      ->orWhereHas('semesterBatches', function ($q2) use ($sembatches_id) {
                          $q2->where('fee_plan_sembatches.sembatches_id', $sembatches_id);
                      });
                })
                ->where(function($q) use ($student) {
                    $q->where('school_id', $student->school_id)
                      ->orWhereNull('school_id');
                })
                ->orderByRaw('school_id DESC')
                ->first();
            }

            $feeAmount = $feePlan ? $feePlan->amount : 0;
            $totalFees += $feeAmount;

            // Prepare student data for PendingStudent compatible record
            $data = $student->toArray();
            $guardian = $student->guardians()->first();
            if ($guardian) {
                $data['guardian_fname'] = $guardian->fname;
                $data['guardian_mname'] = $guardian->mname;
                $data['guardian_lname'] = $guardian->lname;
                $data['guardian_contact'] = $guardian->phone;
                $data['guardian_relation_id'] = $guardian->relation_id;
                $data['guardian_education'] = $guardian->education;
            }

            $studentsData[] = [
                'student_id' => $student->student_id,
                'calculated_fee' => $feeAmount,
                'data' => $data
            ];
        }

        $feePerStudent = $studentCount > 0 ? ($totalFees / $studentCount) : 0;

        // Generate a unique batch code
        $school = \App\Models\School::find($schoolId);
        $schoolCode = $school ? preg_replace('/[^A-Za-z0-9]/', '', substr($school->schoolname, 0, 3)) : 'SCH';
        $batchCode = 'SB-' . strtoupper($schoolCode) . '-' . time() . '-' . rand(10, 99);

        DB::beginTransaction();
        try {
            // 4. Create the UploadBatch record
            $uploadBatch = UploadBatch::create([
                'batch_code' => $batchCode,
                'sembatches_id' => $sembatches_id,
                'school_id' => $schoolId,
                'uploaded_by' => auth()->id(),
                'upload_date' => now(),
                'total_students' => $studentCount,
                'fee_per_student' => $feePerStudent,
                'total_fees' => $totalFees,
                'status' => 'pending',
                'payment_verified' => false,
                'payment_type' => $paymentType,
                'offline_payment_method' => $paymentType === 'offline' ? $request->offline_payment_method : null,
            ]);

            // 5. Create PendingStudent records for this batch
            foreach ($studentsData as $item) {
                PendingStudent::create([
                    'batch_id' => $uploadBatch->batch_id,
                    'calculated_fee' => $item['calculated_fee'],
                    'student_data' => $item['data'],
                    'is_registered' => false,
                ]);
            }

            // 6. Update student records: assign batch_id and fees_status = 'In Progress'
            foreach ($approvedStudents as $student) {
                $student->update([
                    'batch_id' => $uploadBatch->batch_id,
                    'fees_status' => 'In Progress',
                ]);
            }

            DB::commit();

            if ($paymentType === 'offline') {
                return redirect()->route('admin.registrations.index')->with('success', 'Batch ' . $batchCode . ' published successfully! Please complete offline payment verification.');
            } else {
                return redirect()->route('admin.registrations.batch-payment', $uploadBatch->batch_id);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Publish Batch Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to publish batch: ' . $e->getMessage());
        }
    }

    public function batchPayment($batchId)
    {
        $batch = UploadBatch::with(['school', 'semesterBatch'])->findOrFail($batchId);

        $user = auth()->user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            if ($user->hasRole('Educator') && !$user->hasPermission(11601)) {
                abort(403, 'Unauthorized action.');
            }
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($batch->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($batch->payment_verified) {
            return redirect()->route('admin.registrations.index')->with('error', 'This batch payment is already verified.');
        }

        if ($batch->payment_type !== 'online') {
            return redirect()->route('admin.registrations.index')->with('error', 'This batch does not require online payment.');
        }

        try {
            $razorpayService = app(\App\Services\RazorpayService::class);
            $keyId = $razorpayService->getKeyId();
            if (empty($keyId)) {
                return back()->with('error', 'Razorpay configuration is missing on the server.');
            }

            $receipt = 'pub_' . $batch->batch_id . '_' . time();
            $notes = [
                'batch_id' => $batch->batch_id,
                'school_id' => $batch->school_id,
                'sembatches_id' => $batch->sembatches_id,
            ];

            $result = $razorpayService->createOrder($batch->total_fees, 'INR', $receipt, $notes);

            if ($result['success']) {
                $batch->update(['razorpay_order_id' => $result['order_id']]);
                
                $razorpayOrder = [
                    'key' => $keyId,
                    'amount' => $result['amount'],
                    'currency' => $result['currency'],
                    'order_id' => $result['order_id'],
                    'name' => config('app.name', 'Pramukh Academy'),
                    'description' => 'Publish School Registration Batch ' . $batch->batch_code,
                ];

                return view('admin.registrations.batch_payment', compact('batch', 'razorpayOrder'));
            }

            return back()->with('error', 'Could not create Razorpay order: ' . ($result['error'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('Batch Payment Order Creation Exception: ' . $e->getMessage());
            return back()->with('error', 'Error initiating payment: ' . $e->getMessage());
        }
    }

    public function verifyBatchPayment(Request $request, $batchId)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $batch = UploadBatch::findOrFail($batchId);

        $user = auth()->user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            if ($user->hasRole('Educator') && !$user->hasPermission(11601)) {
                abort(403, 'Unauthorized action.');
            }
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($batch->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($batch->payment_verified) {
            return redirect()->route('admin.registrations.index')->with('success', 'Batch payment already verified.');
        }

        try {
            $razorpayService = app(\App\Services\RazorpayService::class);
            $v = $razorpayService->verifyPaymentSignature(
                $request->razorpay_order_id, $request->razorpay_payment_id, $request->razorpay_signature
            );

            if (!$v['success']) {
                return redirect()->route('admin.registrations.index')->with('error', 'Payment verification failed: ' . $v['error']);
            }

            $batch->approveOnlinePayment($request->razorpay_order_id, $request->razorpay_payment_id, $request->razorpay_signature);

            return redirect()->route('admin.registrations.index')->with('success', 'Batch ' . $batch->batch_code . ' payment verified and published successfully!');
        } catch (\Exception $e) {
            Log::error('Verify Batch Payment Exception: ' . $e->getMessage());
            return redirect()->route('admin.registrations.index')->with('error', 'Error verifying payment: ' . $e->getMessage());
        }
    }

    public function approveSchoolPayment(Request $request, $id)
    {
        $student = Student::allCohorts()->findOrFail($id);

        $user = auth()->user();
        if (!$user->hasRole(['Superadmin', 'Admin', 'Accountant']) && !$user->hasPermission(11601)) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->hasRole('School') || $user->hasRole('Educator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        DB::beginTransaction();
        try {
            $student->update([
                'school_payment_status' => 'Approved',
                'school_payment_rejection_reason' => null,
                'verified_by' => $user->fname . ' ' . $user->lname . ' (' . ($user->role->rolename ?? 'Staff') . ')'
            ]);

            app(\App\Services\AuditLoggerService::class)->logCustom(
                'SCHOOL_PAYMENT_APPROVED',
                $student,
                "School payment verified and approved for student {$student->fname} {$student->lname} by {$user->fname} {$user->lname}.",
                null,
                [
                    'verified_by' => $student->verified_by,
                    'school_payment_status' => 'Approved'
                ]
            );

            DB::commit();
            return back()->with('success', 'School payment approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve School Payment Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function rejectSchoolPayment(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $student = Student::allCohorts()->findOrFail($id);

        $user = auth()->user();
        if (!$user->hasRole(['Superadmin', 'Admin', 'Accountant']) && !$user->hasPermission(11601)) {
            abort(403, 'Unauthorized action.');
        }

        if ($user->hasRole('School') || $user->hasRole('Educator')) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        DB::beginTransaction();
        try {
            $student->update([
                'school_payment_status' => 'Rejected',
                'school_payment_rejection_reason' => $request->reason,
                'verified_by' => $user->fname . ' ' . $user->lname . ' (' . ($user->role->rolename ?? 'Staff') . ')'
            ]);

            app(\App\Services\AuditLoggerService::class)->logCustom(
                'SCHOOL_PAYMENT_REJECTED',
                $student,
                "School payment rejected for student {$student->fname} {$student->lname} by {$user->fname} {$user->lname}. Reason: {$request->reason}",
                null,
                [
                    'verified_by' => $student->verified_by,
                    'school_payment_status' => 'Rejected',
                    'rejection_reason' => $request->reason
                ]
            );

            DB::commit();
            return back()->with('success', 'School payment rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reject School Payment Error: ' . $e->getMessage());
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        // Payment-side actions are admin/accountant only; school-side roles
        // approve or reject a registration and nothing more.
        if (!auth()->user()->canRunRegistrationOps()) {
            abort(403, 'Unauthorized action.');
        }
        $studentIds = $this->resolveSelectedStudentIds($request);
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (empty($schoolIds)) {
                $schoolIds = [0];
            }
            if (!empty($studentIds)) {
                $studentIds = Student::whereIn('student_id', $studentIds)
                    ->whereIn('school_id', $schoolIds)
                    ->pluck('student_id')
                    ->toArray();
            }
        }
        
        $query = Student::with([
            'studentType',
            'educationalBoard',
            'school',
            'taluka.district.state',
            'semesterBatches.feePlans',
            'document',
            'creator.role',
            'guardians.relation',
            'standard'
        ]);

        if (!empty($studentIds)) {
            $query->whereIn('student_id', $studentIds);
        } else {

            $user = auth()->user();
            if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
                $schoolIds = $user->getAssignedSchoolIds();
                if (empty($schoolIds)) {
                    $schoolIds = [0];
                }
                $query->whereIn('school_id', $schoolIds);
            }

            $this->applyFilters($request, $query);

            // Apply active tab status filter if present
            $selectedTab = $request->get('status', 'all');
            if ($selectedTab === 'pending_docs') {
                $query->where(function($q) {
                    $q->whereNotIn('application_status', ['Enrolled', 'Approved', 'Rejected', 'Documents Submitted', 'Done'])
                      ->orWhereNull('application_status');
                })
                ->where(function($q) {
                    $q->whereDoesntHave('document')
                      ->orWhereHas('document', function($dq) {
                          $dq->whereNull('photo')->orWhere('photo', '');
                      });
                });
            } elseif ($selectedTab === 'pending') {
                $query->where('application_status', 'Documents Submitted');
            } elseif ($selectedTab === 'processing') {
                $query->where('fees_status', 'In Progress');
            } elseif ($selectedTab === 'enrolled') {
                $query->whereIn('application_status', ['Enrolled', 'Approved', 'Done']);
            } elseif ($selectedTab === 'rejected') {
                $query->where('application_status', 'Rejected');
            } elseif ($selectedTab === 'failed') {
                $query->where(function($q) {
                    $q->where('fees_status', '!=', 'Paid')
                      ->orWhereNull('fees_status');
                })->whereHas('invoices.payments', function($q) {
                    $q->where('status', '3');
                });
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'student_id');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['student_id', 'fname', 'lname', 'created_at', 'fees_status', 'application_status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('student_id', 'desc');
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('error', 'No registrations found to export.');
        }

        // Check if current user can view full data (admin only)
        $canViewFullData = DataMaskingHelper::canViewFullData();

        // Fetch master tables data for mapping IDs to names
        $satsangMandalMap = DB::table('ms_satsangmandal')->pluck('mandalname', 'satsangmandal_id')->toArray();
        $shikharMandirMap = DB::table('ms_shikharmandir')->pluck('mname', 'shikharm_id')->toArray();
        $kshetraMap = DB::table('ms_kshetra')->pluck('kshetraname', 'kshetra_id')->toArray();
        $languagesMap = DB::table('language')->pluck('languagename', 'lg_id')->toArray();
        $feePlansMap = DB::table('fee_plan')->pluck('amount', 'fee_name')->toArray();
        $talukasMap = DB::table('ms_taluko')->pluck('tname', 'takula_id')->toArray();

        $educationMediumMap = [
            '1' => 'Gujarati',
            '2' => 'Hindi',
            '3' => 'English',
            '4' => 'Other'
        ];

        $learnAboutMap = [
            '1' => 'Social Media',
            '2' => 'Friend/Family',
            '3' => 'Website',
            '4' => 'Other'
        ];

        // CSV headers
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="registrations_export_' . date('Y-m-d_H-i-s') . '.csv"',
        ];

        $columns = [
            'Student ID',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email',
            'Contact No',
            'WhatsApp No',
            'Gender',
            'Birth Date',
            'Student Type',
            'Educational Board',
            'Standard / Class',
            'School',
            'Address',
            'City/Village',
            'Pincode',
            'State',
            'District',
            'Taluka',
            'Education Medium',
            'Institute',
            'Achievement Satsang',
            'Achievement Academic',
            'Activities',
            'Learn About',
            'Material Language',
            'Postal Address',
            'Postal City',
            'Postal Taluka',
            'Postal Pincode',
            'Notes',
            'Referred By',
            'Satsang Mandal',
            'Shikhar Mandir',
            'Kshetra',
            'Enrolment No',
            'Fees Status',
            'Application Status',
            'Source',
            'LMS Username',
            'LMS Password',
            'Selected Fee Plan',
            'Fee Plan Amount',
            'Razorpay Order ID',
            'Razorpay Payment ID',
            'Registered Via School Link',
            'School Approval Status',
            'School Payment Method',
            'School Fee Plan',
            'School Fee Amount',
            'School Payment UPI ID',
            'School Payment Status',
            'School Payment Verified By',
            'School Payment Verified At',
            'School Payment Rejection Reason',
            'School Educator Name',
            'School Educator Contact',
            'School Geo Tagging Address',
            'Initiator Contact',
            'Satsang Karyakar Name',
            'Satsang Karyakar Contact',
            'Guardian Name',
            'Guardian Contact',
            'Guardian Email',
            'Guardian Relation',
            'Guardian Education',
            'Guardian Work Experience',
            'Guardian How to Help',
            'Created At',
            'Updated At'
        ];

        $callback = function () use (
            $students,
            $columns,
            $canViewFullData,
            $satsangMandalMap,
            $shikharMandirMap,
            $kshetraMap,
            $languagesMap,
            $educationMediumMap,
            $learnAboutMap,
            $feePlansMap,
            $talukasMap
        ) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($students as $student) {
                // Determine Source
                $sourceLabel = 'Public Form';
                if ($student->createdby === 'Migration_Engine') {
                    $sourceLabel = 'Migrated';
                } elseif ($student->creator) {
                    $role = $student->creator->role->rolename ?? '';
                    if ($role === 'School') {
                        $sourceLabel = 'School Admin';
                    } elseif ($role === 'Initiator') {
                        $sourceLabel = 'Initiator';
                    } elseif ($role === 'Admin') {
                        $sourceLabel = 'Admin (Manual)';
                    } else {
                        $sourceLabel = $role;
                    }
                }

                // Guardian Details
                $guardian = $student->guardians->first();
                $guardianName = $guardian ? trim($guardian->fname . ' ' . $guardian->mname . ' ' . $guardian->lname) : '';
                $guardianPhone = $guardian ? DataMaskingHelper::maskPhone($guardian->phone, $canViewFullData) : '';
                $guardianEmail = $guardian ? DataMaskingHelper::maskEmail($guardian->email, $canViewFullData) : '';
                $guardianRelation = ($guardian && $guardian->relation) ? $guardian->relation->relation : '';
                $guardianEducation = $guardian ? $guardian->education : '';
                $guardianWorkExp = $guardian ? $guardian->work_experience : '';
                $guardianHowToHelp = $guardian ? $guardian->how_to_help : '';

                // Resolved Active Fee Plan
                $activeSemester = $student->semesterBatches
                    ->where('pivot.status', 0)
                    ->sortBy('start_date')
                    ->first();

                $activeFeePlanName = null;
                if ($activeSemester) {
                    $feePlan = $activeSemester->feePlans
                        ->where('fee_name', $student->subscription)
                        ->first();
                    
                    if (!$feePlan && $student->school_id) {
                        $feePlan = $activeSemester->feePlans
                            ->where('school_id', $student->school_id)
                            ->first();
                    }

                    if (!$feePlan) {
                        $feePlan = $activeSemester->feePlans
                            ->whereNull('school_id')
                            ->first();
                    }

                    if ($feePlan) {
                        $activeFeePlanName = $feePlan->fee_name;
                    }
                }

                if (!$activeFeePlanName) {
                    $activeFeePlanName = $student->subscription;
                }

                fputcsv($file, [
                    $student->student_id,
                    $this->escapeCsvField($student->fname),
                    $this->escapeCsvField($student->mname),
                    $this->escapeCsvField($student->lname),
                    $this->escapeCsvField(DataMaskingHelper::maskEmail($student->email, $canViewFullData)),
                    $this->escapeCsvField(DataMaskingHelper::maskPhone($student->contact_no, $canViewFullData)),
                    $this->escapeCsvField(DataMaskingHelper::maskPhone($student->whatsapp_no, $canViewFullData)),
                    $student->gender,
                    $student->birthdate ? $student->birthdate->format('d/m/Y') : '',
                    $student->studentType->studenttypename ?? 'N/A',
                    $student->educationalBoard->boardname ?? 'N/A',
                    $student->standard->stdname ?? 'N/A',
                    $student->school->schoolname ?? $student->school_name ?? 'Not Assigned',
                    $this->escapeCsvField($student->address),
                    $this->escapeCsvField($student->city_village),
                    $student->pincode,
                    $student->state->sname ?? 'N/A',
                    $student->district->dname ?? 'N/A',
                    $student->taluka->tname ?? 'N/A',
                    $educationMediumMap[$student->educationmedium] ?? $student->educationmedium,
                    $this->escapeCsvField($student->institute),
                    $this->escapeCsvField($student->achievement_stsang),
                    $this->escapeCsvField($student->achievement_academic),
                    $this->escapeCsvField($student->activities),
                    $learnAboutMap[$student->learnabout] ?? $student->learnabout,
                    $student->materiallanguage ? ($languagesMap[$student->materiallanguage] ?? $student->materiallanguage) : '',
                    $this->escapeCsvField($student->address_postal),
                    $this->escapeCsvField($student->city_postal),
                    $student->taluka_id_postal ? ($talukasMap[$student->taluka_id_postal] ?? 'N/A') : 'N/A',
                    $student->secondary_pincode,
                    $this->escapeCsvField($student->notes),
                    $this->escapeCsvField($student->refby),
                    $student->satsangmandal_id ? ($satsangMandalMap[$student->satsangmandal_id] ?? $student->satsangmandal_id) : '',
                    $student->shikharm_id ? ($shikharMandirMap[$student->shikharm_id] ?? $student->shikharm_id) : '',
                    $student->kshetra_id ? ($kshetraMap[$student->kshetra_id] ?? $student->kshetra_id) : '',
                    $student->enrolment_no,
                    $student->fees_status,
                    $student->application_status,
                    $sourceLabel,
                    $this->escapeCsvField($student->lmsuser),
                    $this->escapeCsvField($student->lmspswd),
                    $this->escapeCsvField($activeFeePlanName),
                    $activeFeePlanName ? ($feePlansMap[$activeFeePlanName] ?? 'N/A') : 'N/A',
                    $this->escapeCsvField($student->razorpay_order_id),
                    $this->escapeCsvField($student->razorpay_payment_id),
                    $student->registered_via_school_link ? 'Yes' : 'No',
                    $student->school_approval_status,
                    $student->school_payment_method,
                    $this->escapeCsvField($student->school_payment_plan_name),
                    $student->school_payment_amount,
                    $this->escapeCsvField($student->school_payment_upi_id),
                    $student->school_payment_status,
                    $this->escapeCsvField($student->school_payment_verified_by),
                    $student->school_payment_verified_at ? $student->school_payment_verified_at->format('d/m/Y H:i') : '',
                    $this->escapeCsvField($student->school_payment_rejection_reason),
                    $this->escapeCsvField($student->school_educator_name),
                    $this->escapeCsvField(DataMaskingHelper::maskPhone($student->school_educator_no, $canViewFullData)),
                    $this->escapeCsvField($student->geo_tagging_school_address),
                    $this->escapeCsvField(DataMaskingHelper::maskPhone($student->initiator_contact, $canViewFullData)),
                    $this->escapeCsvField($student->satsang_karyakar_name),
                    $this->escapeCsvField(DataMaskingHelper::maskPhone($student->satsang_karyakar_contact, $canViewFullData)),
                    $this->escapeCsvField($guardianName),
                    $this->escapeCsvField($guardianPhone),
                    $this->escapeCsvField($guardianEmail),
                    $this->escapeCsvField($guardianRelation),
                    $this->escapeCsvField($guardianEducation),
                    $this->escapeCsvField($guardianWorkExp),
                    $this->escapeCsvField($guardianHowToHelp),
                    $student->created_at,
                    $student->updated_at
                ]);
            }

            fclose($file);
        };

        return \Response::stream($callback, 200, $headers);
    }

    private function escapeCsvField($field)
    {
        if ($field === null) {
            return '';
        }

        // Replace newlines with spaces
        $field = str_replace(["\r\n", "\r", "\n"], ' ', $field);

        return $field;
    }
}
