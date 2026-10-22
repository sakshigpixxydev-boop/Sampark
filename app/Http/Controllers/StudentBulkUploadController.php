<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentType;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\Taluko;
use App\Models\State;
use App\Models\District;
use App\Models\SemesterBatch;
use App\Models\PaymentMode;
use Illuminate\Support\Facades\Response;
use App\Rules\SecureCsvFile;
use App\Services\FeePlanService;
use App\Services\FileUploadService;
use App\Services\UploadBatchService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StudentBulkUploadController extends Controller
{
    protected $feePlanService;
    protected $uploadBatchService;
    protected $fileUploadService;
    protected $razorpayService;

    public function __construct(
        FeePlanService $feePlanService,
        UploadBatchService $uploadBatchService,
        FileUploadService $fileUploadService,
        RazorpayService $razorpayService
    ) {
        $this->feePlanService = $feePlanService;
        $this->uploadBatchService = $uploadBatchService;
        $this->fileUploadService = $fileUploadService;
        $this->razorpayService = $razorpayService;
    }

    public function showUploadForm()
    {
        $user = Auth::user();
        if ($user && (($user->role->rolename ?? '') === 'School' || $user->hasRole('Educator'))) {
            $school = $user->getAssignedSchool();
        }

        $semesterBatches = SemesterBatch::active()->orderBy('start_date', 'desc')->get();
        $paymentModes = PaymentMode::all();
        $standards = Standard::orderBy('std_id')->get();
        
        $schools = [];
        if ($user) {
            if ($user->hasRole('School') || $user->hasRole('Educator')) {
                $schoolIds = $user->getAssignedSchoolIds();
                $schools = \App\Models\School::whereIn('school_id', $schoolIds)->with('taluka.district')->orderBy('schoolname')->get();
            } elseif ($user->hasRole('Initiator')) {
                $schools = [];
            } else {
                $schools = \App\Models\School::with('taluka.district')->orderBy('schoolname')->get();
            }
        }

        return view('students.bulk-upload', compact('semesterBatches', 'paymentModes', 'standards', 'schools'));
    }

    public function downloadSample()
    {
        $filePath = storage_path('app/samples/sample-student-import-sheet.xlsx');

        if (!file_exists($filePath)) {
            abort(404, 'Sample file not found.');
        }

        return Response::download($filePath, 'student_import_sample.xlsx');
    }

    public function downloadPhysicalForm()
    {
        $filePath = storage_path('app/samples/physical-student-form.pdf');

        if (!file_exists($filePath)) {
            abort(404, 'Physical form PDF not found.');
        }

        return Response::download($filePath, 'physical_student_registration_form.pdf');
    }

    public function upload(Request $request)
    {
        Log::info('Bulk upload: Request reached controller', [
            'sembatches_id' => $request->sembatches_id,
            'payment_type' => $request->payment_type,
            'fee_plan_id' => $request->fee_plan_id,
            'user_id' => Auth::id()
        ]);

        $user = Auth::user();
        if ($user && (($user->role->rolename ?? '') === 'School' || $user->hasRole('Educator'))) {
            $school = $user->getAssignedSchool();
        }

        // Validate the request with secure file validation
        $request->validate([
            'csv_file' => ['required', 'file', new SecureCsvFile()],
            'sembatches_id' => 'required|exists:sembatches,sembatches_id',
            'payment_type' => 'required|in:online,offline',
            'offline_payment_method' => 'required_if:payment_type,offline|nullable|in:bank_transfer,cheque,other',
            'payment_document' => 'nullable|file|mimetypes:application/pdf,image/jpeg,image/jpg,image/png,image/gif|max:5120',
            'fee_plan_id' => 'nullable|exists:fee_plan,fee_plan_id',
        ]);

        $file = $request->file('csv_file');
        $semesterBatchId = $request->sembatches_id;
        $paymentType = $request->payment_type;
        $feePlanId = $request->fee_plan_id;

        Log::info('Bulk upload: Input details', [
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'payment_type' => $paymentType
        ]);

        try {
            Log::info('Bulk upload: Starting file parsing');
            $parsedData = $this->parseFile($file);

            $csvData = $parsedData['students'];
            $initiatorData = $parsedData['initiator'] ?? [];

            Log::info('Bulk upload: Data parsed successfully', [
                'students_count' => count($csvData),
                'initiator_count' => count($initiatorData)
            ]);

            if (empty($csvData)) {
                Log::warning('Bulk upload: No student data found in file');
                return back()->withErrors(['csv_file' => 'The file is empty or invalid.']);
            }

            // Consolidate school and initiator info from logged-in user if missing
            $schoolId = $request->school_id; // Check manual selection first

            // Enforce school isolation for School / Educator
            if ($user && ($user->hasRole('School') || $user->hasRole('Educator'))) {
                $assignedSchoolIds = $user->getAssignedSchoolIds();
                if ($schoolId && !in_array($schoolId, $assignedSchoolIds)) {
                    abort(403, 'Unauthorized action.');
                }
                if (!$schoolId) {
                    $schoolId = $assignedSchoolIds[0] ?? null;
                }
            } else {
                if (!$schoolId && $user && ($user->role->rolename ?? '') === 'Initiator') {
                    $assignedSchoolIds = $user->getAssignedSchoolIds();
                    if (empty($assignedSchoolIds)) {
                        return back()->withErrors(['csv_file' => 'Error: You do not have any schools assigned. Please contact the administrator.']);
                    }
                    $schoolId = $assignedSchoolIds[0];
                }
            }
            $school = $schoolId ? \App\Models\School::find($schoolId) : null;

            // Auto-populate Initiator Data if sheet was empty/missing
            if (empty($initiatorData) && $school && $school->initiator) {
                $init = $school->initiator;
                $initiatorData = [[
                    'first_name' => $init->fname,
                    'middle_name' => $init->mname,
                    'last_name' => $init->lname,
                    'initiator_code' => $init->refcode ?: $init->username,
                    'enroll_school_name' => $school->schoolname,
                    'school_address' => $school->address1 . ', ' . $school->address2,
                    'contact_no' => $init->phone,
                    'email' => $init->email,
                    'enroll_school_date' => now()->format('Y-m-d'),
                ]];
            }

            // Normalize initiator dates
            foreach ($initiatorData as &$initData) {
                foreach (['enroll_school_date', 'tentative_date_full_payment'] as $dateField) {
                    if (!empty($initData[$dateField])) {
                        if (is_numeric($initData[$dateField])) {
                            $initData[$dateField] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($initData[$dateField])->format('Y-m-d');
                        } else {
                            $d = \DateTime::createFromFormat('d-m-Y', $initData[$dateField]);
                            if ($d) {
                                $initData[$dateField] = $d->format('Y-m-d');
                            }
                        }
                    }
                }
            }
            unset($initData);

            // Consolidate lookups using helper method
            $lookupResult = $this->getLookupData();
            $lookups = $lookupResult['validation'];
            $rawLookups = $lookupResult['raw'];

            // Helper to resolve ID from Name or fallback to existing ID/Value
            $resolveId = function (&$item, $keyName, $keyId, $map) {
                if (empty($item[$keyId]) && !empty($item[$keyName])) {
                    $rawName = trim($item[$keyName]);
                    $lookupName = strtolower($rawName);
                    if (isset($map[$lookupName])) {
                        $item[$keyId] = $map[$lookupName];
                    } elseif (is_numeric($rawName)) {
                        $item[$keyId] = $rawName;
                    }
                }
            };

            $mediums = ['gujarati' => '1', 'hindi' => '2', 'english' => '3', 'other' => '4'];

            $batch = SemesterBatch::find($semesterBatchId);
            $batchStdId = $batch ? $batch->std_id : null;

            // Remap human-readable headers to *_id with lookup logic
            foreach ($csvData as $index => &$row) {
                // Ensure target keys exist before any access
                foreach (['studenttype_id', 'eduboard_id', 'state_id', 'dist_id', 'takula_id', 'institute', 'school_name', 'school_address', 'guardian_relation_id', 'shikharm_id'] as $key) {
                    $row[$key] = $row[$key] ?? null;
                }
                
                // Sanitize phone/whatsapp numbers
                if (!empty($row['whatsapp_no'])) {
                    $row['whatsapp_no'] = $this->sanitizePhoneNumber($row['whatsapp_no']);
                }
                if (!empty($row['guardian_contact'])) {
                    $row['guardian_contact'] = $this->sanitizePhoneNumber($row['guardian_contact']);
                }
                
                // Assign standard ID: Sheet value takes priority, then Batch default
                $resolveId($row, 'standard', 'std_id', $rawLookups['standards']);
                if (empty($row['std_id'])) {
                    $row['std_id'] = $batchStdId;
                }

                // Auto-fill from logged-in user
                if ($schoolId) {
                    $row['school_id'] = $schoolId;
                    if ($school) {
                        $row['state_id'] = $row['state_id'] ?: $school->taluka->district->state_id ?? null;
                        $row['dist_id'] = $row['dist_id'] ?: $school->taluka->dist_id ?? null;
                        $row['takula_id'] = $row['takula_id'] ?: $school->takula_id;
                        $row['institute'] = $row['institute'] ?: $school->schoolname;
                        $row['school_name'] = $row['school_name'] ?: $school->schoolname;
                        $row['school_address'] = $row['school_address'] ?: $school->address1 . ', ' . $school->address2;
                    }
                }

                // Auto-generate Enrolment No if missing, using the standard format
                if (empty($row['enrolment_no'])) {
                    $row['enrolment_no'] = \App\Models\Student::generateNextEnrolmentNumber(null, $index);
                }

                $resolveId($row, 'student_type', 'studenttype_id', $rawLookups['student_types']);
                $resolveId($row, 'educational_board', 'eduboard_id', $rawLookups['educational_boards']);
                $resolveId($row, 'state', 'state_id', $rawLookups['states']);
                $resolveId($row, 'district', 'dist_id', $rawLookups['districts']);
                $resolveId($row, 'taluka', 'takula_id', $rawLookups['talukas']);
                $resolveId($row, 'guardian_relation', 'guardian_relation_id', $rawLookups['relations']);
                $resolveId($row, 'shikharabddha_mandir', 'shikharm_id', $rawLookups['shikharmandirs']);

                // If initiator code is provided in the student sheet, use it for linking
                if (!empty($row['initiator_code'])) {
                    $row['initiator_contact'] = $row['initiator_code'];
                } elseif ($user && ($user->role->rolename ?? '') === 'Initiator') {
                    // Fallback to uploader's phone if they are the initiator
                    $row['initiator_contact'] = $user->phone;
                }

                // Ensure non-baps (ID 2) is the default
                if (empty($row['studenttype_id'])) $row['studenttype_id'] = 2;

                if (!empty($row['gender'])) {
                    $val = strtolower(trim($row['gender']));
                    if (in_array($val, ['male', 'm'])) $row['gender'] = 'M';
                    elseif (in_array($val, ['female', 'f'])) $row['gender'] = 'F';
                    elseif (in_array($val, ['other', 'o'])) $row['gender'] = 'O';
                }

                if (!empty($row['education_medium'])) {
                    $val = strtolower(trim($row['education_medium']));
                    $row['educationmedium'] = $mediums[$val] ?? null;
                }

                $resolveId($row, 'material_language', 'materiallanguage', $rawLookups['languages']);
            }
            unset($row);

            // Validate structure - ensure basic mandatory fields are present as keys
            $requiredColumns = ['fname', 'lname', 'email']; 
            $headers = array_keys($csvData[0] ?? []);
            
            \Log::debug('Bulk Upload Headers Found:', ['headers' => $headers]);

            $missingColumns = array_diff($requiredColumns, $headers);
            if (!empty($missingColumns)) {
                \Log::error('Bulk Upload Missing Columns:', ['missing' => $missingColumns]);
                return back()->withErrors([
                    'csv_file' => 'Missing required columns: ' . implode(', ', $missingColumns)
                ]);
            }

            // Check row limit - Increase to 500
            if (count($csvData) > 500) {
                return back()->withErrors([
                    'csv_file' => 'Maximum 500 students allowed per upload. Your file contains ' . count($csvData) . ' rows.'
                ]);
            }

            // Validate each row and calculate fees
            $validatedData = [];
            $errors = [];

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2;

                $rowErrors = $this->validateRow($row, $rowNumber, $lookups, $semesterBatchId);
                if (!empty($rowErrors)) {
                    $errors[$rowNumber] = $rowErrors;
                }

                // Normalize birthdate
                if (!empty($row['birthdate'])) {
                    $d = \DateTime::createFromFormat('d-m-Y', $row['birthdate']);
                    if (!$d) {
                        $d = \DateTime::createFromFormat('d/m/Y', $row['birthdate']);
                    }
                    if (!$d && is_numeric($row['birthdate'])) {
                        $d = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['birthdate']);
                    } elseif (!$d) {
                        $d = \DateTime::createFromFormat('Y-m-d', $row['birthdate']);
                    }
                    if ($d) {
                        $row['birthdate'] = $d->format('Y-m-d');
                    }
                }

                $feeCalculation = $this->feePlanService->calculateFees($row, $semesterBatchId, $feePlanId);

                $validatedData[] = [
                    'row_number' => $rowNumber,
                    'data' => $row,
                    'errors' => $rowErrors,
                    'calculated_fee' => $feeCalculation['total_due'],
                    'fee_breakdown' => $feeCalculation,
                ];

            }

            $batchTotals = $this->feePlanService->calculateBatchTotal($validatedData, $semesterBatchId, $feePlanId);

            $paymentDocumentPath = null;
            if ($paymentType === 'offline' && $request->hasFile('payment_document')) {
                $paymentDocumentPath = $this->fileUploadService->storePublicFile($request->file('payment_document'), 'payment_docs');
            }


            $this->setBulkUploadData($validatedData);

            session([
                'bulk_upload_sembatches_id' => $semesterBatchId,
                'bulk_upload_has_errors' => !empty($errors),
                'bulk_upload_totals' => $batchTotals,
                'bulk_upload_payment_type' => $paymentType,
                'bulk_upload_offline_payment_method' => $request->offline_payment_method,
                'bulk_upload_fee_plan_id' => $feePlanId,
                'bulk_upload_payment_document_path' => $paymentDocumentPath,
                'bulk_upload_school_id' => $schoolId,
                'bulk_upload_initiator_data' => $initiatorData,
            ]);

            return redirect()->route('students.bulk-upload.preview');

        } catch (\Throwable $e) {
            Log::error('Bulk upload: Fatal error in upload method: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['csv_file' => 'A fatal error occurred: ' . $e->getMessage()]);
        }
    }

    private function parseFile($file)
    {
        if (!extension_loaded('zip') && str_ends_with(strtolower($file->getClientOriginalName()), '.xlsx')) {
            Log::error('Bulk upload: PHP zip extension is missing. Required for .xlsx files.');
            throw new \Exception('PHP zip extension is missing on the server. Please contact administrator.');
        }

        try {
            $path = $file->getRealPath();
            Log::info('parseFile: Loading spreadsheet from ' . $path, [
                'memory_before' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
            ]);

            // Optimization: Use PhpSpreadsheet directly to precisely control memory
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
            
            // Only load required sheets. This skips the heavy "data" and "validation" sheets (indices 1 and 2).
            $reader->setLoadSheetsOnly(['import', 'Student', 'student', 'initiator', 'Sheet1']); 
            $reader->setReadDataOnly(true); // Don't parse formatting/styles
            
            $spreadsheet = $reader->load($path);
            
            // Get data from sheets
            // Get data from sheets
            $studentsSheet = $spreadsheet->getSheetByName('import') 
                ?? $spreadsheet->getSheetByName('Student') 
                ?? $spreadsheet->getSheetByName('student') 
                ?? $spreadsheet->getSheetByName('Sheet1') ;
            
            $studentsData = $studentsSheet ? $studentsSheet->toArray() : [];
            
            $initiatorsSheet = $spreadsheet->getSheetByName('initiator') 
                ?? $spreadsheet->getSheetByName('Initiator');
            
            $initiators = $initiatorsSheet ? $initiatorsSheet->toArray() : [];

            // If still empty and we have multiple sheets, the first one is usually the student sheet
            if (empty($studentsData) && $spreadsheet->getSheetCount() > 0) {
                $studentsData = $spreadsheet->getSheet(0)->toArray();
            }
            if (empty($initiators) && $spreadsheet->getSheetCount() > 1) {
                $initiators = $spreadsheet->getSheet(1)->toArray();
            }

            $students = $this->processSheet($studentsData, 'student');
            $initiatorsRaw = $this->processSheet($initiators, 'initiator');
            
            // Filter out completely empty rows
            $initiator = [];
            foreach ($initiatorsRaw as $row) {
                if (!empty(array_filter($row, function($value) { return $value !== null && $value !== ''; }))) {
                    $initiator[] = $row;
                }
            }

            // Cleanup
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $reader);

            return [
                'students' => $students,
                'initiator' => $initiator
            ];
        } catch (\Throwable $e) {
            Log::error('Bulk upload: Error parsing file: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'memory_at_error' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB'
            ]);
            throw $e;
        }
    }

    private function processSheet($rows, $type)
    {
        if (empty($rows)) return [];

        $headers = array_shift($rows);
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $mapping = ($type === 'student') ? [
            'guardian first name' => 'guardian_fname',
            'guardian middle name' => 'guardian_mname',
            'guardian last name' => 'guardian_lname',
            'guardian gender' => 'guardian_gender',
            'guardian relation' => 'guardian_relation',
            'guardian primary contact' => 'guardian_contact',
            'guardian contact' => 'guardian_contact',
            'guardian name' => 'guardian_name',
            'gurdian first name' => 'guardian_fname',
            'gurdian middle name' => 'guardian_mname',
            'gurdian last name' => 'guardian_lname',
            'gurdian gender' => 'guardian_gender',
            'gurdian relation' => 'guardian_relation',
            'gurdian contact' => 'guardian_contact',
            'gurdian name' => 'guardian_name',
            'guardian first name' => 'guardian_fname',
            'guardian middle name' => 'guardian_mname',
            'guardian last name' => 'guardian_lname',
            'guardian gender' => 'guardian_gender',
            'guardian relation' => 'guardian_relation',
            'guardian contact' => 'guardian_contact',
            'guardian name' => 'guardian_name',
            'first name' => 'fname', 
            'middle name' => 'mname', 
            'surname' => 'lname',
            'last name' => 'lname',
            'birthdate' => 'birthdate',
            'medium of education' => 'education_medium', 
            'education board' => 'educational_board',
            'gender' => 'gender',
            'address' => 'address', 
            'city village' => 'city_village',
            'state' => 'state',
            'district' => 'district',
            'takula' => 'taluka',
            'taluka' => 'taluka',
            'pincode' => 'pincode',
            'whatsapp' => 'whatsapp_no',
            'study material language' => 'material_language',
            'subscription' => 'subscription',
            'standard' => 'standard',
            // Additional fields to ensure zero data loss
            'activities' => 'activities',
            'achievements' => 'achievement_stsang',
            'academic achievements' => 'achievement_academic',
            'guardian education' => 'guardian_education',
            'shikharabddha mandir' => 'shikharabddha_mandir',
            // Fallbacks
            'enrolment no' => 'enrolment_no',
            'email' => 'email',
            'initiator code' => 'initiator_code',
        ] : [
            'geo tagging' => 'geo_tagging_school_address',
            'geotag' => 'geo_tagging_school_address',
            'initiator code' => 'initiator_code', 'first name' => 'first_name', 'middle name' => 'middle_name',
            'surname' => 'last_name', 'gender' => 'gender', 'whatsapp' => 'whatsapp_contact',
            'email' => 'email', 'enroll school date' => 'enroll_school_date',
            'enroll school name' => 'enroll_school_name', 'school address' => 'school_address',
            'contact person' => 'school_contact_person',
            'position' => 'position', 'contact no' => 'contact_no', 'total enroll' => 'total_enroll_student',
            'payment received' => 'payment_received', 'mode of payment' => 'mode_of_payment',
            'payment pending' => 'payment_pending', 'honorarium' => 'honorarium',
            'tentative date' => 'tentative_date_full_payment',
        ];

        $mappedHeaders = [];
        foreach ($headers as $index => $header) {
            $mapped = false;
            // Normalize header: lowercase and strip all non-alphanumeric characters
            $normHeader = preg_replace('/[^a-z0-9]/', '', $header);
            
            foreach ($mapping as $keyword => $key) {
                // Normalize keyword: strip all non-alphanumeric characters
                $normKeyword = preg_replace('/[^a-z0-9]/', '', $keyword);
                
                if ($normHeader === $normKeyword || str_contains($normHeader, $normKeyword)) {
                    $mappedHeaders[$index] = $key;
                    $mapped = true;
                    break;
                }
            }
            if (!$mapped) $mappedHeaders[$index] = "temp_$index";
        }

        $data = [];
        $emptyRowCount = 0;
        foreach ($rows as $row) {
            if (empty(array_filter($row))) {
                $emptyRowCount++;
                if ($emptyRowCount > 20) break;
                continue;
            }
            $emptyRowCount = 0;

            $item = [];
            foreach ($row as $index => $value) {
                $key = $mappedHeaders[$index] ?? "temp_$index";
                if (str_starts_with($key, 'temp_')) continue;
                $item[$key] = $value;
            }
            $data[] = $item;
        }

        return $data;
    }

    private function validateRow($row, $rowNumber, $lookups = [], $semesterBatchId = null)
    {
        $errors = [];
        $mandatory = ['fname' => 'First Name', 'mname' => 'Middle Name', 'lname' => 'Last Name', 'email' => 'Email'];
        foreach ($mandatory as $field => $label) {
            if (empty($row[$field])) $errors[] = "{$label} is required";
        }

        if (!empty($row['email']) && !filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (!empty($row['email'])) {
            $email = strtolower(trim($row['email']));
            if (isset($lookups['existing_emails'][$email])) {
                $errors[] = "Email already exists in the system";
            }
        }

        // Standard is now optional
        /*
        if (empty($row['std_id'])) {
            $errors[] = "Standard is required (not found in batch)";
        }
        */

        if (!empty($row['gender'])) {
            $val = strtoupper($row['gender']);
            if (!in_array($val, ['M', 'F', 'O'])) $errors[] = "Gender must be M, F, or O";
        }

        // Clean and validate whatsapp_no
        if (!empty($row['whatsapp_no'])) {
            $whatsapp = preg_replace('/\D/', '', $row['whatsapp_no']);
            if (strlen($whatsapp) === 12 && str_starts_with($whatsapp, '91')) {
                $whatsapp = substr($whatsapp, 2);
            } elseif (strlen($whatsapp) === 11 && str_starts_with($whatsapp, '0')) {
                $whatsapp = substr($whatsapp, 1);
            }
            if (strlen($whatsapp) !== 10) {
                $errors[] = "WhatsApp number must be exactly 10 digits";
            }
        }

        // Clean and validate guardian_contact
        if (!empty($row['guardian_contact'])) {
            $contact = preg_replace('/\D/', '', $row['guardian_contact']);
            if (strlen($contact) === 12 && str_starts_with($contact, '91')) {
                $contact = substr($contact, 2);
            } elseif (strlen($contact) === 11 && str_starts_with($contact, '0')) {
                $contact = substr($contact, 1);
            }
            if (strlen($contact) !== 10) {
                $errors[] = "Guardian contact number must be exactly 10 digits";
            }
        }

        return $errors;
    }

    public function preview()
    {
        if (!session()->has('bulk_upload_data_stored')) {
            return redirect()->route('students.bulk-upload.form')->withErrors(['error' => 'No upload data found.']);
        }

        $data = [
            'students' => $this->getBulkUploadData(),
            'semesterBatch' => SemesterBatch::find(session('bulk_upload_sembatches_id')),
            'hasErrors' => session('bulk_upload_has_errors'),
            'totals' => session('bulk_upload_totals'),
            'studentTypes' => StudentType::orderBy('studenttypename')->get(),
            'educationalBoards' => EducationalBoard::orderBy('boardname')->get(),
            'standards' => Standard::orderBy('std_id')->get(),
            'talukos' => Taluko::orderBy('tname')->get(),
            'initiatorData' => session('bulk_upload_initiator_data'),
        ];

        return view('students.bulk-upload-preview', $data);
    }

    public function updatePreview(Request $request)
    {
        $students = $this->getBulkUploadData();
        $semesterBatchId = session('bulk_upload_sembatches_id');
        $feePlanId = session('bulk_upload_fee_plan_id');
        $rowNumber = $request->input('row_number');
        $updatedData = $request->except(['_token', 'row_number']);

        foreach ($students as &$student) {
            if ($student['row_number'] == $rowNumber) {
                if (isset($updatedData['whatsapp_no'])) {
                    $updatedData['whatsapp_no'] = $this->sanitizePhoneNumber($updatedData['whatsapp_no']);
                }
                if (isset($updatedData['guardian_contact'])) {
                    $updatedData['guardian_contact'] = $this->sanitizePhoneNumber($updatedData['guardian_contact']);
                }
                $student['data'] = array_merge($student['data'], $updatedData);
                $lookupResult = $this->getLookupData();
                $student['errors'] = $this->validateRow($student['data'], $rowNumber, $lookupResult['validation'], $semesterBatchId);
                
                if (!empty($student['data']['birthdate'])) {
                    $d = \DateTime::createFromFormat('d-m-Y', $student['data']['birthdate']);
                    if (!$d) {
                        $d = \DateTime::createFromFormat('d/m/Y', $student['data']['birthdate']);
                    }
                    if (!$d) {
                        $d = \DateTime::createFromFormat('Y-m-d', $student['data']['birthdate']);
                    }
                    if ($d) {
                        $student['data']['birthdate'] = $d->format('Y-m-d');
                    }
                }

                $fee = $this->feePlanService->calculateFees($student['data'], $semesterBatchId, $feePlanId);
                $student['calculated_fee'] = $fee['total_due'];
                $student['fee_breakdown'] = $fee;
                break;
            }
        }

        $batchTotals = $this->feePlanService->calculateBatchTotal($students, $semesterBatchId, $feePlanId);
        $hasErrors = collect($students)->contains(fn($s) => !empty($s['errors']));

        $this->setBulkUploadData($students);
        session([
            'bulk_upload_has_errors' => $hasErrors,
            'bulk_upload_totals' => $batchTotals,
        ]);

        return response()->json(['success' => true, 'has_errors' => $hasErrors, 'totals' => $batchTotals]);
    }

    public function submitForVerification(Request $request)
    {
        if (!session()->has('bulk_upload_data_stored') || session('bulk_upload_has_errors')) {
            return back()->withErrors(['error' => 'Invalid session or unresolved errors.']);
        }

        $students = $this->getBulkUploadData();

        $uploadBatch = $this->uploadBatchService->createBatch(
            $students,
            session('bulk_upload_sembatches_id'),
            Auth::id(),
            session('bulk_upload_totals'),
            [
                'payment_type' => session('bulk_upload_payment_type'),
                'offline_payment_method' => session('bulk_upload_offline_payment_method'),
                'payment_document_path' => session('bulk_upload_payment_document_path'),
                'school_id' => session('bulk_upload_school_id'),
                'initiator_data' => session('bulk_upload_initiator_data'),
            ]
        );

        $this->forgetBulkUploadData();
        session()->forget([
            'bulk_upload_sembatches_id', 'bulk_upload_has_errors',
            'bulk_upload_totals', 'bulk_upload_payment_type', 'bulk_upload_offline_payment_method',
            'bulk_upload_payment_document_path', 'bulk_upload_school_id', 'bulk_upload_initiator_data',
        ]);

        return redirect()->route('students.index')->with('success', "Batch {$uploadBatch->batch_code} submitted.");
    }

    public function getFeePlans(Request $request)
    {
        $query = \App\Models\FeePlan::where('sembatches_id', $request->semester_batch_id);

        return response()->json($query->where('valid_from', '<=', now())
            ->where(function ($q) { $q->whereNull('valid_to')->orWhere('valid_to', '>=', now()); })
            ->get());
    }

    public function createRazorpayOrder(Request $request)
    {
        if (!session()->has('bulk_upload_totals')) {
            Log::error('Razorpay Order: Session totals missing');
            return response()->json(['error' => 'Upload session expired. Please re-upload.'], 400);
        }

        $totals = session('bulk_upload_totals');
        $amount = $totals['total_fees'];

        if ($amount <= 0) {
            Log::error('Razorpay Order: Invalid amount', ['amount' => $amount]);
            return response()->json(['error' => 'Amount must be greater than zero.'], 400);
        }

        try {
            $receipt = 'bulk_' . time();
            $notes = [
                'user_id' => Auth::id(),
                'student_count' => $totals['student_count'] ?? 0,
                'semester_batch_id' => session('bulk_upload_sembatches_id'),
            ];

            $keyId = $this->razorpayService->getKeyId();
            if (empty($keyId)) {
                Log::error('Razorpay Order: RAZORPAY_KEY_ID is missing in .env');
                return response()->json(['error' => 'Razorpay configuration is missing on the server. Please contact administrator.'], 500);
            }

            $result = $this->razorpayService->createOrder($amount, 'INR', $receipt, $notes);

            if ($result['success']) {
                $response = [
                    'key' => $this->razorpayService->getKeyId(),
                    'amount' => $result['amount'],
                    'currency' => $result['currency'],
                    'order_id' => $result['order_id'],
                    'name' => config('app.name', 'Pramukh Sankalp'),
                    'description' => 'Bulk Student Upload Fee',
                ];
                return response()->json($response);
            }

            Log::error('Razorpay Order Creation Failed', ['error' => $result['error']]);
            return response()->json(['error' => $result['error']], 400);
        } catch (\Exception $e) {
            Log::error('Razorpay Order: Exception', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Could not create order: ' . $e->getMessage()], 500);
        }
    }

    public function verifyRazorpayPayment(Request $request)
    {
        $v = $this->razorpayService->verifyPaymentSignature(
            $request->razorpay_order_id, $request->razorpay_payment_id, $request->razorpay_signature
        );

        if (!$v['success']) return back()->with('error', 'Payment failed: ' . $v['error']);
        if (!session()->has('bulk_upload_data_stored')) return redirect()->route('students.index')->with('error', 'Expired.');

        try {
            $students = $this->getBulkUploadData();
            $batch = $this->uploadBatchService->createBatch(
                $students,
                session('bulk_upload_sembatches_id'),
                Auth::id(),
                session('bulk_upload_totals'),
                [
                    'payment_type' => 'online',
                    'school_id' => session('bulk_upload_school_id'),
                    'initiator_data' => session('bulk_upload_initiator_data'),
                ]
            );
            
            $batch->approveOnlinePayment($request->razorpay_order_id, $request->razorpay_payment_id, $request->razorpay_signature);
            
            $this->forgetBulkUploadData();
            session()->forget([
                'bulk_upload_sembatches_id', 'bulk_upload_has_errors',
                'bulk_upload_totals', 'bulk_upload_payment_type', 'bulk_upload_offline_payment_method',
                'bulk_upload_payment_document_path', 'bulk_upload_school_id', 'bulk_upload_initiator_data',
            ]);
            
            return redirect()->route('students.index')->with('success', "Batch {$batch->batch_code} processed.");
        } catch (\Exception $e) {
            Log::error('Razorpay Verification Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function getLookupData()
    {
        $studentTypes = StudentType::pluck('studenttype_id', 'studenttypename')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        $educationalBoards = EducationalBoard::pluck('eduboard_id', 'boardname')
            ->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        $rawStandards = Standard::all();
        $standards = [];
        $validationStandards = [];

        foreach ($rawStandards as $std) {
            $name = strtolower(trim($std->stdname));
            $standards[$name] = $std->std_id;
            $validationStandards[$std->std_id] = $std->stdname;
            
            // Map purely numeric match (e.g. "Standard 5" -> "5")
            if (preg_match('/Standard\s+(\d+)/i', $std->stdname, $matches)) {
                $standards[$matches[1]] = $std->std_id;
            }
        }

        $states = State::pluck('state_id', 'sname')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        $districts = District::pluck('dist_id', 'dname')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        $talukas = Taluko::pluck('takula_id', 'tname')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        $relations = \App\Models\Relation::pluck('relation_id', 'relation')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        $languages = \Illuminate\Support\Facades\DB::table('language')->pluck('lg_id', 'languagename')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        $shikharmandirs = \Illuminate\Support\Facades\DB::table('ms_shikharmandir')->pluck('shikharm_id', 'mname')->mapWithKeys(fn($id, $name) => [strtolower($name) => $id])->toArray();
        
        return [
            'raw' => [
                'student_types' => $studentTypes,
                'educational_boards' => $educationalBoards,
                'standards' => $standards,
                'states' => $states,
                'districts' => $districts,
                'talukas' => $talukas,
                'relations' => $relations,
                'languages' => $languages,
                'shikharmandirs' => $shikharmandirs,
            ],
            'validation' => [
                'student_types' => array_flip($studentTypes),
                'educational_boards' => array_flip($educationalBoards),
                'standards' => $validationStandards,
                'states' => array_flip($states),
                'districts' => array_flip($districts),
                'talukas' => array_flip($talukas),
                'languages' => array_flip($languages),
                'shikharmandirs' => array_flip($shikharmandirs),
                'existing_emails' => Student::selectRaw('LOWER(email) as email')->pluck('email')->flip()->toArray()
            ]
        ];
    }

    private function sanitizePhoneNumber($phone)
    {
        if (empty($phone)) return '';
        $cleaned = preg_replace('/\D/', '', $phone);
        if (strlen($cleaned) === 12 && str_starts_with($cleaned, '91')) {
            $cleaned = substr($cleaned, 2);
        }
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '0')) {
            $cleaned = substr($cleaned, 1);
        }
        return $cleaned;
    }

    private function getBulkUploadDataFilePath()
    {
        $userId = Auth::id() ?: 'guest';
        $dir = storage_path('app/temp');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir . '/bulk_upload_' . $userId . '.json';
    }

    private function setBulkUploadData($data)
    {
        $path = $this->getBulkUploadDataFilePath();
        file_put_contents($path, json_encode($data));
        session(['bulk_upload_data_stored' => true]);
    }

    private function getBulkUploadData()
    {
        $path = $this->getBulkUploadDataFilePath();
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?: [];
        }
        return [];
    }

    private function forgetBulkUploadData()
    {
        $path = $this->getBulkUploadDataFilePath();
        if (file_exists($path)) {
            @unlink($path);
        }
        session()->forget('bulk_upload_data_stored');
    }
}
