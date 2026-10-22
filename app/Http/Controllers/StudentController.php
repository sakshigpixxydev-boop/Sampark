<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentType;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\School;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Taluko;
use App\Models\SysUser;
use App\Models\SemesterBatch;
use App\Services\FilterService;
use App\Http\Traits\HandlesErrors;
use App\Helpers\DataMaskingHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    use HandlesErrors;

    protected $filterService;

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index(Request $request)
    {
        try {
            // Merge request filters with session filters
            $filters = $this->filterService->mergeFilters($request, 'students');

            // Check if clear filters was requested
            if ($request->has('clear_filters')) {
                $this->filterService->clearFilters('students');
                return redirect()->route('students.index');
            }

            // Determine school/student restrictions
            $userSchoolIds = [];
            $assignedStudentIds = [];
            $isSchoolUser = Auth::user()->hasRole('School') || Auth::user()->hasRole('Educator');
            $isInitiatorUser = Auth::user()->hasRole('Initiator');
            
            if ($isSchoolUser) {
                $userSchoolIds = Auth::user()->getAssignedSchoolIds();
                
                // Pre-fill filter if single school
                if (count($userSchoolIds) === 1) {
                     $filters['school_id'] = $userSchoolIds[0];
                }
            } elseif ($isInitiatorUser) {
                $user = Auth::user();
                $userSchoolIds = $user->getAssignedSchoolIds();
                $assignedStudentIds = $user->getAssignedStudentIds();
            }

            $query = Student::with([
                'studentType',
                'educationalBoard',
                'school',
                'taluka.district.state',
                'users'
            ])->whereIn('application_status', ['Enrolled', 'Approved', 'Done']);

            // Apply Role Restrictions (Security)
            if ($isSchoolUser) {
                if (empty($userSchoolIds)) {
                    $query->whereRaw('1 = 0'); // Force empty result if no schools assigned
                } else {
                    $query->whereIn('school_id', $userSchoolIds);
                }
            } elseif ($isInitiatorUser) {
                if (empty($assignedStudentIds)) {
                    $query->whereRaw('1 = 0'); // Force empty result if no students assigned
                } else {
                    $query->whereIn('student_id', $assignedStudentIds);
                }
            }

            // Define filter map
            $filterMap = [
                'school_id' => ['type' => 'exact', 'column' => 'school_id'],
                'user_id' => [
                    'type' => 'relation',
                    'relation' => 'users',
                    'relation_column' => 'sysuser.sysuser_id'
                ],
                'payment_status' => [
                    'type' => 'relation',
                    'relation' => 'invoices',
                    'relation_column' => 'status'
                ],
                'city_village' => ['type' => 'like', 'column' => 'city_village'],
                'city_village' => ['type' => 'like', 'column' => 'city_village'],
                'eduboard_id' => ['type' => 'in', 'column' => 'eduboard_id'],
                'studenttype_id' => ['type' => 'in', 'column' => 'studenttype_id'],
                'sembatches_id' => [
                    'type' => 'relation',
                    'relation' => 'semesterBatches',
                    'relation_column' => 'student_sembatches.sembatches_id'
                ],
                'source' => [
                    'type' => 'custom',
                    'callback' => function ($query, $value) {
                        $source = strtolower($value);
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
                ],
                'search' => [
                    'type' => 'custom',
                    'callback' => function ($query, $value) {
                        $query->where(function ($q) use ($value) {
                            $q->where('fname', 'like', "%{$value}%")
                                ->orWhere('mname', 'like', "%{$value}%")
                                ->orWhere('lname', 'like', "%{$value}%")
                                ->orWhere('email', 'like', "%{$value}%");
                        });
                    }
                ],
            ];

            // Apply filters using the service
            $query = $this->filterService->applyFilters($query, $filters, $filterMap);

            // Apply sorting
            $sortBy = $request->get('sort_by', 'fname');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            $students = $query->paginate(15)->withQueryString();

            // Get filter options
            if ($isSchoolUser || $isInitiatorUser) {
                $schools = School::whereIn('school_id', $userSchoolIds)
                    ->where('status', 1)
                    ->orderBy('schoolname')
                    ->get();
            } else {
                $schools = School::where('status', 1)->orderBy('schoolname')->get();
            }
            $users = SysUser::orderBy('fname')->get();
            $educationalBoards = EducationalBoard::orderBy('boardname')->get();
            $studentTypes = StudentType::orderBy('studenttypename')->get();
            $batches = SemesterBatch::orderBy('start_date', 'desc')->get();
            $sources = [
                'form' => 'Public Form',
                'School' => 'School Admin',
                'Initiator' => 'Initiator',
                'Admin' => 'Admin (Manual)'
            ];

            return view('students.index', compact(
                'students',
                'schools',
                'users',
                'educationalBoards',
                'studentTypes',
                'batches',
                'sources',
                'filters'
            ));
        } catch (\Exception $e) {
            return $this->handleError($e, 'Failed to load students list.');
        }
    }

    public function create()
    {
        // Check if user has write permission (10042)
        if (!Auth::user()->hasPermission(10042)) {
            abort(403, 'You don\'t have permission to create students.');
        }

        // Check if school user's school is approved (removed restriction as per user request)
        $user = Auth::user();
        if ($user && $user->hasRole('School')) {
            $school = $user->getAssignedSchool();
            // We allow access even if not approved now
        }

        $studentTypes = StudentType::orderBy('studenttypename')->get();
        $educationalBoards = EducationalBoard::orderBy('boardname')->get();
        $standards = Standard::orderBy('std_id')->get();
        $schools = School::where('inactive', 0)->orderBy('schoolname')->get();
        $countries = Country::orderBy('cname')->get();
        $languages = DB::table('language')->orderBy('languagename')->get();

        return view('students.create', compact(
            'studentTypes',
            'educationalBoards',
            'standards',
            'schools',
            'countries',
            'languages'
        ));
    }

    public function store(Request $request)
    {
        // Check if user has write permission (10042)
        if (!Auth::user()->hasPermission(10042)) {
            abort(403, 'You don\'t have permission to create students.');
        }

        // Check if school user's school is approved
        $user = Auth::user();
        if ($user && $user->hasRole('School')) {
            $school = $user->getAssignedSchool();
            if ($school && $school->status != 1) {
                return redirect()->route('dashboard')->with('error', 'School under process in approval.');
            }
        }

        try {
            $validated = $request->validate([
                // Mandatory fields
                'studenttype_id' => 'required|exists:studenttype,studenttype_id',
                'eduboard_id' => 'required|exists:ms_eductionalboard,eduboard_id',
                'std_id' => 'nullable|exists:ms_standard,std_id',
                'state_id' => 'required|exists:ms_state,state_id',
                'district_id' => 'required|exists:ms_dist,dist_id',
                'takula_id' => 'required|exists:ms_taluko,takula_id',
                'fname' => 'required|string|max:100',
                'mname' => 'required|string|max:100',
                'lname' => 'required|string|max:100',
                'email' => 'required|email|max:100|unique:student,email',

                // Optional fields
                'school_id' => 'nullable|exists:school,school_id',
                'gender' => 'nullable|in:M,F,O',
                'birthdate' => 'nullable|date_format_dmy',
                'address' => 'nullable|string|max:500',
                'city_village' => 'nullable|string|max:100',
                'pincode' => 'nullable|digits:6',
                'contact_no' => 'nullable|string|min:7|max:15',
                'whatsapp_no' => 'nullable|string|min:7|max:15',
                'educationmedium' => 'nullable|in:1,2,3,4', // ENUM values
                'institute' => 'nullable|string|max:200',
                'achievement_stsang' => 'nullable|string',
                'achievement_academic' => 'nullable|string',
                'activities' => 'nullable|string',
                'learnabout' => 'nullable|in:1,2,3,4', // ENUM values
                'materiallanguage' => 'nullable|string|max:50',
                'taluka_id_postal' => 'nullable|exists:ms_taluko,takula_id',
                'city_postal' => 'nullable|string|max:100',
                'address_postal' => 'nullable|string|max:500',
                'notes' => 'nullable|string',
                'refby' => 'nullable|string|max:200',
                'satsangmandal_id' => 'nullable|integer',
                'shikharm_id' => 'nullable|integer',
                'kshetra_id' => 'nullable|integer',
            ]);

            DB::beginTransaction();

            // Set created by
            $validated['createdby'] = Auth::id();
            
            // Convert birthdate from DD/MM/YYYY to YYYY-MM-DD
            if (isset($validated['birthdate'])) {
                $validated['birthdate'] = \App\Helpers\DateHelper::convertDateFormat($validated['birthdate']);
            }
            
            // Map district_id to dist_id
            if (isset($validated['district_id'])) {
                $validated['dist_id'] = $validated['district_id'];
                unset($validated['district_id']);
            }

            $student = Student::create($validated);

            DB::commit();

            return redirect()->route('guardians.create', ['student_id' => $student->student_id])
                ->with('success', 'Student "' . $student->fname . ' ' . $student->lname . '" created successfully. Please add guardian details.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e; // Let Laravel handle validation errors
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, 'Failed to create student. Please try again.');
        }
    }

    public function edit(Student $student)
    {
        // Check if user has write permission (10042) or Registration review permission (11201)
        if (!Auth::user()->hasPermission(10042) && !Auth::user()->hasPermission(11201) && !Auth::user()->hasPermission(10001) && !Auth::user()->hasPermission(11602)) {
            abort(403, 'You don\'t have permission to edit students.');
        }

        // Check if school user's school is approved (removed restriction as per user request)
        $user = Auth::user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $studentTypes = StudentType::orderBy('studenttypename')->get();
        $educationalBoards = EducationalBoard::orderBy('boardname')->get();
        $standards = Standard::orderBy('std_id')->get();
        $schools = School::where('inactive', 0)->orderBy('schoolname')->get();
        $countries = Country::orderBy('cname')->get();
        $languages = DB::table('language')->orderBy('languagename')->get();

        // Get the location hierarchy for the student
        $selectedTaluka = $student->taluka;
        $selectedDistrict = $student->district ?? ($selectedTaluka ? $selectedTaluka->district : null);
        $selectedState = $student->state ?? ($selectedDistrict ? $selectedDistrict->state : null);
        $selectedCountry = $selectedState ? $selectedState->country : null;

        // Get related location data
        $states = $selectedCountry ? State::where('country_id', $selectedCountry->country_id)->orderBy('sname')->get() : collect();
        $districts = $selectedState ? District::where('state_id', $selectedState->state_id)->orderBy('dname')->get() : collect();
        $talukos = $selectedDistrict ? Taluko::where('dist_id', $selectedDistrict->dist_id)->orderBy('tname')->get() : collect();

        // Get postal location hierarchy if different
        $selectedPostalTaluka = $student->taluka_id_postal ? Taluko::find($student->taluka_id_postal) : null;
        $selectedPostalDistrict = $selectedPostalTaluka ? $selectedPostalTaluka->district : null;
        $selectedPostalState = $selectedPostalDistrict ? $selectedPostalDistrict->state : null;
        $selectedPostalCountry = $selectedPostalState ? $selectedPostalState->country : null;

        $postalStates = $selectedPostalCountry ? State::where('country_id', $selectedPostalCountry->country_id)->orderBy('sname')->get() : collect();
        $postalDistricts = $selectedPostalState ? District::where('state_id', $selectedPostalState->state_id)->orderBy('dname')->get() : collect();
        $postalTalukos = $selectedPostalDistrict ? Taluko::where('dist_id', $selectedPostalDistrict->dist_id)->orderBy('tname')->get() : collect();

        return view('students.edit', compact(
            'student',
            'studentTypes',
            'educationalBoards',
            'standards',
            'schools',
            'countries',
            'states',
            'districts',
            'talukos',
            'selectedCountry',
            'selectedState',
            'selectedDistrict',
            'selectedTaluka',
            'postalStates',
            'postalDistricts',
            'postalTalukos',
            'selectedPostalCountry',
            'selectedPostalState',
            'selectedPostalDistrict',
            'selectedPostalTaluka',
            'languages'
        ));
    }

    public function update(Request $request, Student $student)
    {
        // Check if user has write permission (10042) or Registration review permission (11201)
        if (!Auth::user()->hasPermission(10042) && !Auth::user()->hasPermission(11201) && !Auth::user()->hasPermission(10001) && !Auth::user()->hasPermission(11602)) {
            abort(403, 'You don\'t have permission to update students.');
        }

        // Check if school user's school is approved (removed restriction as per user request)
        $user = Auth::user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $validated = $request->validate([
            // Mandatory fields
            'studenttype_id' => 'required|exists:studenttype,studenttype_id',
            'registered_via_school_link' => 'nullable|boolean',
            'eduboard_id' => 'required|exists:ms_eductionalboard,eduboard_id',
            'std_id' => 'nullable|exists:ms_standard,std_id',
            'state_id' => 'required|exists:ms_state,state_id',
            'district_id' => 'required|exists:ms_dist,dist_id',
            'takula_id' => 'required|exists:ms_taluko,takula_id',
            'fname' => 'required|string|max:100',
            'mname' => 'required|string|max:100',
            'lname' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('student', 'email')->ignore($student->student_id, 'student_id')
            ],

            // Optional fields
            'school_id' => 'nullable|exists:school,school_id',
            'gender' => 'nullable|in:M,F,O',
            'birthdate' => 'nullable|date_format_dmy',
            'address' => 'nullable|string|max:500',
            'city_village' => 'nullable|string|max:100',
            'pincode' => 'nullable|digits:6',
            'contact_no' => 'nullable|string|min:7|max:15',
            'whatsapp_no' => 'nullable|string|min:7|max:15',
            'educationmedium' => 'nullable|in:1,2,3,4',
            'institute' => 'nullable|string|max:200',
            'achievement_stsang' => 'nullable|string',
            'achievement_academic' => 'nullable|string',
            'activities' => 'nullable|string',
            'learnabout' => 'nullable|string|max:200',
            'materiallanguage' => 'nullable|string|max:50',
            'taluka_id_postal' => 'nullable|exists:ms_taluko,takula_id',
            'city_postal' => 'nullable|string|max:100',
            'address_postal' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'refby' => 'nullable|string|max:200',
            'satsangmandal_id' => 'nullable|integer',
            'shikharm_id' => 'nullable|integer',
            'kshetra_id' => 'nullable|integer',
        ]);

        // Convert birthdate from DD/MM/YYYY to YYYY-MM-DD
        if (isset($validated['birthdate'])) {
            $validated['birthdate'] = \App\Helpers\DateHelper::convertDateFormat($validated['birthdate']);
        }

        // Map district_id to dist_id
        if (isset($validated['district_id'])) {
            $validated['dist_id'] = $validated['district_id'];
            unset($validated['district_id']);
        }

        $student->update($validated);

        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }

    /**
     * Export students to CSV with filters
     */
    public function export(Request $request)
    {
        $query = Student::with([
            'studentType',
            'educationalBoard',
            'school',
            'taluka.district.state',
            'users'
        ]);

        $user = Auth::user();
        if ($user && $user->hasRole('Educator') && !$user->hasPermission(11603)) {
            abort(403, 'Unauthorized action.');
        }

        // Determine school/student restrictions based on role
        $userSchoolIds = [];
        $assignedStudentIds = [];
        $isSchoolUser = Auth::user()->hasRole('School') || Auth::user()->hasRole('Educator');
        $isInitiatorUser = Auth::user()->hasRole('Initiator');

        if ($isSchoolUser) {
            $userSchoolIds = Auth::user()->getAssignedSchoolIds();
            if (empty($userSchoolIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('school_id', $userSchoolIds);
            }
        } elseif ($isInitiatorUser) {
            $user = Auth::user();
            $userSchoolIds = $user->getAssignedSchoolIds();
            $assignedStudentIds = $user->getAssignedStudentIds();
            if (empty($assignedStudentIds)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('student_id', $assignedStudentIds);
            }
        }

        // Apply filters
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('user_id')) {
            $query->whereHas('users', function ($q) use ($request) {
                $q->where('sysuser.sysuser_id', $request->user_id);
            });
        }

        if ($request->filled('payment_status')) {
            $query->whereHas('invoices', function ($q) use ($request) {
                $q->where('status', $request->payment_status);
            });
        }

        if ($request->filled('city_village')) {
            $query->where('city_village', 'like', '%' . $request->city_village . '%');
        }



        if ($request->filled('eduboard_id')) {
            $boardIds = is_array($request->eduboard_id) ? $request->eduboard_id : [$request->eduboard_id];
            $query->whereIn('eduboard_id', $boardIds);
        }

        if ($request->filled('studenttype_id')) {
            $typeIds = is_array($request->studenttype_id) ? $request->studenttype_id : [$request->studenttype_id];
            $query->whereIn('studenttype_id', $typeIds);
        }

        if ($request->filled('sembatches_id')) {
            $query->whereHas('semesterBatches', function ($q) use ($request) {
                $q->where('student_sembatches.sembatches_id', $request->sembatches_id);
            });
        }

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

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('fname', 'like', '%' . $request->search . '%')
                    ->orWhere('mname', 'like', '%' . $request->search . '%')
                    ->orWhere('lname', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('start_date')) {
            $startDate = \App\Helpers\DateHelper::convertDateFormat($request->start_date);
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = \App\Helpers\DateHelper::convertDateFormat($request->end_date);
            $query->whereDate('created_at', '<=', $endDate);
        }

        $students = $query->orderBy('student_id', 'desc')->get();

        if ($students->isEmpty()) {
            return redirect()->route('students.index')->with('error', 'No students found for export.');
        }

        // Check if current user can view full data (admin only)
        $canViewFullData = DataMaskingHelper::canViewFullData();

        // Fetch master tables data for mapping IDs to names
        $satsangMandalMap = DB::table('ms_satsangmandal')->pluck('mandalname', 'satsangmandal_id')->toArray();
        $shikharMandirMap = DB::table('ms_shikharmandir')->pluck('mname', 'shikharm_id')->toArray();
        $kshetraMap = DB::table('ms_kshetra')->pluck('kshetraname', 'kshetra_id')->toArray();
        $languagesMap = DB::table('language')->pluck('languagename', 'lg_id')->toArray();

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
            'Content-Disposition' => 'attachment; filename="students_export_' . date('Y-m-d_H-i-s') . '.csv"',
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
            $learnAboutMap
        ) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($students as $student) {
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
                    $student->school->schoolname ?? 'Not Assigned',
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
                    $student->taluka_id_postal ? Taluko::find($student->taluka_id_postal)->tname ?? 'N/A' : 'N/A',
                    $student->secondary_pincode,
                    $this->escapeCsvField($student->notes),
                    $this->escapeCsvField($student->refby),
                    $student->satsangmandal_id ? ($satsangMandalMap[$student->satsangmandal_id] ?? $student->satsangmandal_id) : '',
                    $student->shikharm_id ? ($shikharMandirMap[$student->shikharm_id] ?? $student->shikharm_id) : '',
                    $student->kshetra_id ? ($kshetraMap[$student->kshetra_id] ?? $student->kshetra_id) : '',
                    $student->enrolment_no,
                    $student->fees_status,
                    $student->application_status,
                    $student->created_at,
                    $student->updated_at
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Escape CSV field to handle commas, quotes, and newlines
     */
    private function escapeCsvField($field)
    {
        if ($field === null) {
            return '';
        }

        // Replace newlines with spaces
        $field = str_replace(["\r\n", "\r", "\n"], ' ', $field);

        return $field;
    }

    public function show($id)
    {
        if (!Auth::user()->hasPermission(10041) && !Auth::user()->hasPermission(11201) && !Auth::user()->hasPermission(10001) && !Auth::user()->hasPermission(11602) && !Auth::user()->hasPermission(11604)) {
            abort(403, 'You don\'t have permission to view student details.');
        }

        $student = Student::allCohorts()->withTrashed()->findOrFail($id);

        $user = Auth::user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $student->load([
            'guardians.relation',
            'semesterBatches.feePlans',
            'state',
            'district',
            'taluka',
            'school',
            'document'
        ]);

        $languagesMap = Student::getLanguagesMap();
        $educationMediumMap = Student::getEducationMediumMap();
        $learnAboutMap = Student::getLearnAboutMap();

        return view('students.show', compact('student', 'languagesMap', 'educationMediumMap', 'learnAboutMap'));
    }

    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->hasPermission(10042) && !Auth::user()->hasPermission(11201) && !Auth::user()->hasPermission(10001) && !Auth::user()->hasPermission(11602)) {
            abort(403, 'You don\'t have permission to delete students.');
        }

        $student = Student::allCohorts()->findOrFail($id);

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $user = Auth::user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

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
        if (!Auth::user()->hasPermission(10042) && !Auth::user()->hasPermission(11201) && !Auth::user()->hasPermission(10001) && !Auth::user()->hasPermission(11602)) {
            abort(403, 'You don\'t have permission to restore students.');
        }

        $student = Student::allCohorts()->onlyTrashed()->findOrFail($id);

        $user = Auth::user();
        if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
            $schoolIds = $user->getAssignedSchoolIds();
            if (!in_array($student->school_id, $schoolIds)) {
                abort(403, 'Unauthorized action.');
            }
        }

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
}