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

class MigratedStudentUploadController extends Controller
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
        return view('students.migrated-upload');
    }



    public function upload(Request $request)
    {
        // Validate the request
        $request->validate([
            'csv_file' => ['required', 'file', new SecureCsvFile()],
        ]);

        $file = $request->file('csv_file');

        try {
            $parsedData = $this->parseFile($file);

            $csvData = $parsedData['students'];
            $initiatorData = $parsedData['initiator'] ?? [];

            if (empty($csvData)) {
                return back()->withErrors(['csv_file' => 'The file is empty or invalid.']);
            }

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

            $allFeePlans = \App\Models\FeePlan::all()->keyBy('fee_name');
            $allSemesterBatches = \App\Models\SemesterBatch::all()->groupBy('display_name');

            // Remap human-readable headers to *_id with lookup logic
            foreach ($csvData as $index => &$row) {
                // Ensure target keys exist before any access
                foreach (['studenttype_id', 'eduboard_id', 'state_id', 'dist_id', 'takula_id', 'institute', 'school_name', 'school_address', 'guardian_relation_id', 'shikharm_id', 'std_id'] as $key) {
                    $row[$key] = $row[$key] ?? null;
                }
                
                // Sanitize phone/whatsapp numbers
                if (!empty($row['whatsapp_no'])) {
                    $row['whatsapp_no'] = $this->sanitizePhoneNumber($row['whatsapp_no']);
                }
                if (!empty($row['guardian_contact'])) {
                    $row['guardian_contact'] = $this->sanitizePhoneNumber($row['guardian_contact']);
                }
                
                $resolveId($row, 'standard', 'std_id', $rawLookups['standards']);

                // Map roll_number to enrolment_no if roll_number is present
                if (!empty($row['roll_number'])) {
                    $row['enrolment_no'] = trim($row['roll_number']);
                } else {
                    $row['enrolment_no'] = null;
                }

                // Resolve migration fee plan and semester batches
                $resolvedMapping = $this->resolveMigrationPlanAndBatches(
                    $row['batch_name'] ?? null,
                    $row['plan_name'] ?? null,
                    $row['roll_number'] ?? null,
                    $row['sheet_type'] ?? null,
                    $allFeePlans,
                    $allSemesterBatches
                );
                
                if ($resolvedMapping) {
                    $row['subscription'] = $resolvedMapping['subscription'];
                    $row['fee_plan_id'] = $resolvedMapping['fee_plan_id'];
                    $row['sembatches_ids'] = $resolvedMapping['sembatches_ids'];
                    $row['fees_status'] = $resolvedMapping['fees_status'] ?? 'Paid';
                } else {
                    $row['subscription'] = null;
                    $row['fee_plan_id'] = null;
                    $row['sembatches_ids'] = [];
                    $row['fees_status'] = 'Paid';
                }

                $resolveId($row, 'student_type', 'studenttype_id', $rawLookups['student_types']);
                $resolveId($row, 'educational_board', 'eduboard_id', $rawLookups['educational_boards']);
                $resolveId($row, 'state', 'state_id', $rawLookups['states']);
                $resolveId($row, 'district', 'dist_id', $rawLookups['districts']);
                $resolveId($row, 'taluka', 'takula_id', $rawLookups['talukas']);
                $resolveId($row, 'guardian_relation', 'guardian_relation_id', $rawLookups['relations']);
                $resolveId($row, 'shikharabddha_mandir', 'shikharm_id', $rawLookups['shikharmandirs']);

                // Ensure non-baps (ID 2) is the default
                if (empty($row['studenttype_id'])) $row['studenttype_id'] = 2;

                if (!empty($row['gender'])) {
                    $val = strtolower(trim($row['gender']));
                    if (in_array($val, ['male', 'm', '1'])) $row['gender'] = 'M';
                    elseif (in_array($val, ['female', 'f', '2'])) $row['gender'] = 'F';
                    elseif (in_array($val, ['other', 'o', '3'])) $row['gender'] = 'O';
                }

                if (!empty($row['education_medium'])) {
                    $val = strtolower(trim($row['education_medium']));
                    $row['educationmedium'] = $mediums[$val] ?? null;
                }

                $resolveId($row, 'material_language', 'materiallanguage', $rawLookups['languages']);
            }
            unset($row);

            $requiredColumns = ['fname', 'lname', 'email']; 
            $headers = array_keys($csvData[0] ?? []);
            $missingColumns = array_diff($requiredColumns, $headers);
            if (!empty($missingColumns)) {
                return back()->withErrors([
                    'csv_file' => 'Missing required columns: ' . implode(', ', $missingColumns)
                ]);
            }

            if (count($csvData) > 500) {
                return back()->withErrors([
                    'csv_file' => 'Maximum 500 students allowed per upload. Your file contains ' . count($csvData) . ' rows.'
                ]);
            }

            $validatedData = [];
            $errors = [];

            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2;

                $rowErrors = $this->validateRow($row, $rowNumber, $lookups, null);
                if (!empty($rowErrors)) {
                    $errors[$rowNumber] = $rowErrors;
                }

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

                $validatedData[] = [
                    'row_number' => $rowNumber,
                    'data' => $row,
                    'errors' => $rowErrors,
                ];
            }

            $this->setBulkUploadData($validatedData);

            session([
                'migrated_upload_has_errors' => !empty($errors),
            ]);

            return redirect()->route('migrated-students.upload.preview');

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
            $reader->setLoadSheetsOnly(['import', 'Student', 'student', 'initiator', 'Sheet1', '1-year', '6-months', '1-Year', '6-Months', '1 Year', '6 Months']); 
            $reader->setReadDataOnly(true); // Don't parse formatting/styles
            
            $spreadsheet = $reader->load($path);
            
            // Get data from sheets
            $studentsData = [];
            $yearSheetNames = ['1-year', '1-Year', '1 Year'];
            $monthSheetNames = ['6-months', '6-Months', '6 Months'];
            
            $yearSheet = null;
            foreach ($yearSheetNames as $name) {
                if ($spreadsheet->getSheetByName($name)) {
                    $yearSheet = $spreadsheet->getSheetByName($name);
                    break;
                }
            }
            
            $monthSheet = null;
            foreach ($monthSheetNames as $name) {
                if ($spreadsheet->getSheetByName($name)) {
                    $monthSheet = $spreadsheet->getSheetByName($name);
                    break;
                }
            }

            $students = [];
            if ($yearSheet) {
                $yearData = $yearSheet->toArray();
                $processed = $this->processSheet($yearData, 'student');
                foreach ($processed as &$row) {
                    $row['sheet_type'] = '1-year';
                    if (empty($row['plan_name'])) {
                        $row['plan_name'] = 'For 12 months';
                    }
                }
                $students = array_merge($students, $processed);
            }
            
            if ($monthSheet) {
                $monthData = $monthSheet->toArray();
                $processed = $this->processSheet($monthData, 'student');
                foreach ($processed as &$row) {
                    $row['sheet_type'] = '6-months';
                    if (empty($row['plan_name'])) {
                        $row['plan_name'] = 'For 6 months';
                    }
                }
                $students = array_merge($students, $processed);
            }

            if (!$yearSheet && !$monthSheet) {
                $studentsSheet = $spreadsheet->getSheetByName('import') 
                    ?? $spreadsheet->getSheetByName('Student') 
                    ?? $spreadsheet->getSheetByName('student') 
                    ?? $spreadsheet->getSheetByName('Sheet1') ;
                
                $studentsRaw = $studentsSheet ? $studentsSheet->toArray() : [];
                if (empty($studentsRaw) && $spreadsheet->getSheetCount() > 0) {
                    $studentsRaw = $spreadsheet->getSheet(0)->toArray();
                }
                $students = $this->processSheet($studentsRaw, 'student');
            }
            
            $initiatorsSheet = $spreadsheet->getSheetByName('initiator') 
                ?? $spreadsheet->getSheetByName('Initiator');
            
            $initiators = $initiatorsSheet ? $initiatorsSheet->toArray() : [];
            if (empty($initiators) && $spreadsheet->getSheetCount() > 1 && !$yearSheet && !$monthSheet) {
                $initiators = $spreadsheet->getSheet(1)->toArray();
            }

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
            'guardian first name new' => 'guardian_fname',
            'guardian middle name new' => 'guardian_mname',
            'guardian last name new' => 'guardian_lname',
            'guardian name new' => 'guardian_name',
            'guardian contact no' => 'guardian_contact',
            'state name' => 'state',
            'district name' => 'district',
            'taluka name' => 'taluka',
            'roll number' => 'roll_number',
            'birth' => 'birthdate',
            'study preference lang' => 'material_language',
            'satsang achievements' => 'achievement_stsang',
            'academic achievements' => 'achievement_academic',
            'extracurricular activities' => 'activities',
            'plan name' => 'plan_name',
            'batch name' => 'batch_name',

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
        // First pass: Exact matches
        foreach ($headers as $index => $header) {
            $normHeader = preg_replace('/[^a-z0-9]/', '', $header);
            foreach ($mapping as $keyword => $key) {
                $normKeyword = preg_replace('/[^a-z0-9]/', '', $keyword);
                if ($normHeader === $normKeyword) {
                    $mappedHeaders[$index] = $key;
                    break;
                }
            }
        }

        // Second pass: Fallback matches (only for unmatched columns)
        foreach ($headers as $index => $header) {
            if (isset($mappedHeaders[$index])) continue;

            $normHeader = preg_replace('/[^a-z0-9]/', '', $header);

            // Skip secondary, previous, and postal columns to avoid mapping overrides
            if (str_contains($normHeader, 'previous') || str_contains($normHeader, 'secondary') || str_contains($normHeader, 'postal')) {
                $mappedHeaders[$index] = "temp_$index";
                continue;
            }

            $mapped = false;
            foreach ($mapping as $keyword => $key) {
                $normKeyword = preg_replace('/[^a-z0-9]/', '', $keyword);
                if (str_contains($normHeader, $normKeyword)) {
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

    private function resolveMigrationPlanAndBatches($batchName, $planName, $rollNumber, $sheetType = null, $allFeePlans = null, $allSemesterBatches = null)
    {
        // 1. Determine batch category (SNK-JAN25 or SNK-JAN26)
        $batchCategory = null;
        
        $rollPrefix = '';
        if (!empty($rollNumber) && preg_match('/^(SNK-[A-Z0-9]+)-/i', trim($rollNumber), $m)) {
            $rollPrefix = strtoupper($m[1]);
        }
        
        $normalizedBatch = strtoupper(trim($batchName ?? ''));
        
        if ($rollPrefix === 'SNK-JAN26') {
            $batchCategory = 'SNK-JAN26';
        } elseif ($rollPrefix === 'SNK-JAN25' || $rollPrefix === 'SNK-JUL25') {
            $batchCategory = 'SNK-JAN25';
        } elseif (strpos($normalizedBatch, 'JAN26') !== false) {
            $batchCategory = 'SNK-JAN26';
        } elseif (strpos($normalizedBatch, 'JAN25') !== false || strpos($normalizedBatch, 'JUL25') !== false) {
            $batchCategory = 'SNK-JAN25';
        }
        
        // 2. Determine duration category (6-months or 1-year)
        $duration = null;
        $normalizedPlan = strtolower(trim($planName ?? ''));
        $normalizedSheet = strtolower(trim($sheetType ?? ''));
        
        if (strpos($normalizedPlan, '6 month') !== false || $normalizedSheet === '6-months') {
            $duration = '6-months';
        } elseif (strpos($normalizedPlan, '12 month') !== false || strpos($normalizedPlan, '1 year') !== false || $normalizedSheet === '1-year') {
            $duration = '1-year';
        }
        
        // 3. Map to fee plan and semester batches names
        $feePlanName = null;
        $batchNames = [];
        
        if ($batchCategory === 'SNK-JAN25') {
            if ($duration === '6-months') {
                $feePlanName = 'Semester-05 (July 2026 to Dec 2026)';
                $batchNames = ['Semester-5 (July 2026)'];
            } elseif ($duration === '1-year') {
                $feePlanName = 'Semester-05 & 06 (July 2026 to June 2027)';
                $batchNames = ['Semester-5 (July 2026)', 'Semester-6 (January 2027)'];
            }
        } elseif ($batchCategory === 'SNK-JAN26') {
            if ($duration === '6-months') {
                $feePlanName = 'Semester-02 (July 2026 to Dec 2026)';
                $batchNames = ['Semester-2 (July 2026)'];
            } elseif ($duration === '1-year') {
                $feePlanName = 'Semester-02 & 03 (July 2026 to June 2027)';
                $batchNames = ['Semester-2 (July 2026)', 'Semester-3 (January 2027)'];
            }
        }

        if ($feePlanName) {
            $feePlan = $allFeePlans ? ($allFeePlans[$feePlanName] ?? null) : \App\Models\FeePlan::where('fee_name', $feePlanName)->first();
            
            $sembatchesIds = [];
            if (!empty($batchNames)) {
                if ($allSemesterBatches) {
                    foreach ($batchNames as $bName) {
                        if (isset($allSemesterBatches[$bName])) {
                            foreach ($allSemesterBatches[$bName] as $batch) {
                                $sembatchesIds[] = $batch->sembatches_id;
                            }
                        }
                    }
                } else {
                    $sembatchesIds = \App\Models\SemesterBatch::whereIn('display_name', $batchNames)
                        ->pluck('sembatches_id')
                        ->toArray();
                }
            }
            
            if ($feePlan) {
                return [
                    'subscription' => $feePlan->fee_name,
                    'fee_plan_id' => $feePlan->fee_plan_id,
                    'sembatches_ids' => $sembatchesIds,
                    'fees_status' => ($duration === '1-year') ? 'Paid' : 'Unpaid'
                ];
            }
        }
        
        return null;
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
                $createdBy = $lookups['existing_emails'][$email];
                if ($createdBy !== 'Migration_Engine') {
                    $errors[] = "Email already exists in the system";
                }
            }
        }



        // Validate migration fee plan assignment
        if (empty($row['subscription']) || empty($row['sembatches_ids'])) {
            $errors[] = "Cannot align batch '" . ($row['batch_name'] ?? 'N/A') . "' and plan '" . ($row['plan_name'] ?? 'N/A') . "' with any existing fee plans.";
        }

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

        if (empty($row['state_id'])) {
            $errors[] = "State is required or could not be matched";
        }
        if (empty($row['dist_id'])) {
            $errors[] = "District is required or could not be matched";
        }
        if (empty($row['takula_id'])) {
            $errors[] = "Taluka is required or could not be matched";
        }

        return $errors;
    }

    public function preview()
    {
        if (!session()->has('bulk_upload_data_stored')) {
            return redirect()->route('migrated-students.upload.form')->withErrors(['error' => 'No upload data found.']);
        }

        $data = [
            'students' => $this->getBulkUploadData(),
            'hasErrors' => session('migrated_upload_has_errors'),
            'studentTypes' => StudentType::orderBy('studenttypename')->get(),
            'educationalBoards' => EducationalBoard::orderBy('boardname')->get(),
            'standards' => Standard::orderBy('std_id')->get(),
            'talukos' => Taluko::orderBy('tname')->get(),
            'states' => State::orderBy('sname')->get(),
            'districts' => District::orderBy('dname')->get(),
            'sembatches' => SemesterBatch::all(),
        ];

        return view('students.migrated-upload-preview', $data);
    }

    public function submitForVerification(Request $request)
    {
        // Prevent timeout for large migration data sets
        set_time_limit(300);

        if (!session()->has('bulk_upload_data_stored') || session('migrated_upload_has_errors')) {
            return back()->withErrors(['error' => 'Invalid session or unresolved errors.']);
        }

        $studentsData = $this->getBulkUploadData();

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $invoiceService = app(\App\Services\InvoiceService::class);
            $studentRole = \App\Models\Role::where('rolename', 'Student')->first();

            foreach ($studentsData as $studentItem) {
                $studentData = $studentItem['data'];
                
                // Add createdby flag
                $studentData['createdby'] = 'Migration_Engine';
                $studentData['application_status'] = 'Enrolled';
                $studentData['fees_status'] = $studentData['fees_status'] ?? 'Paid';
                $studentData['is_email_verified'] = 1;

                // Ensure some defaults
                $studentData['studenttype_id'] = $studentData['studenttype_id'] ?? 2;
                $studentData['eduboard_id'] = $studentData['eduboard_id'] ?? 1;
                $studentData['std_id'] = $studentData['std_id'] ?? 1;
                
                // Extract custom fields not directly mapped to student table columns
                $sembatchesIds = $studentData['sembatches_ids'] ?? [];
                $hasExplicitRollNumber = !empty($studentData['roll_number']);
                
                unset($studentData['sembatches_ids']);
                unset($studentData['fee_plan_id']);
                unset($studentData['sheet_type']);
                unset($studentData['plan_name']);
                unset($studentData['batch_name']);
                unset($studentData['roll_number']);

                // Check if exists by email
                $student = null;
                if (!empty($studentData['email'])) {
                    $student = Student::where('email', strtolower(trim($studentData['email'])))->first();
                }

                if ($student) {
                    if (!$hasExplicitRollNumber && !empty($student->enrolment_no)) {
                        unset($studentData['enrolment_no']);
                    }
                    $student->update($studentData);
                } else {
                    $student = Student::create($studentData);
                }

                $student_id = $student->student_id;

                // Sync with student_sembatches pivot
                if (!empty($sembatchesIds)) {
                    foreach ($sembatchesIds as $sembatches_id) {
                        $exists = \DB::table('student_sembatches')
                            ->where('student_id', $student_id)
                            ->where('sembatches_id', $sembatches_id)
                            ->exists();
                        if (!$exists) {
                            \DB::table('student_sembatches')->insert([
                                'student_id' => $student_id,
                                'sembatches_id' => $sembatches_id,
                                'status' => 0, // Active
                                'assigned_at' => now(),
                            ]);
                        }
                    }
                }

                // Create Guardian if present
                if (!empty($studentData['guardian_fname']) || !empty($studentData['guardian_lname']) || !empty($studentData['guardian_name'])) {
                    $gFname = $studentData['guardian_fname'] ?? null;
                    $gMname = $studentData['guardian_mname'] ?? null;
                    $gLname = $studentData['guardian_lname'] ?? null;
                    
                    if (empty($gFname) && !empty($studentData['guardian_name'])) {
                        $gNameParts = explode(' ', trim($studentData['guardian_name']));
                        $gFname = $gNameParts[0] ?? 'Guardian';
                        $gLname = (count($gNameParts) > 1) ? end($gNameParts) : ($studentData['lname'] ?? '');
                        $gMname = (count($gNameParts) > 2) ? implode(' ', array_slice($gNameParts, 1, -1)) : '';
                    }

                    $existingGuardian = \App\Models\Guardian::where('student_id', $student_id)->first();
                    if ($existingGuardian) {
                        $existingGuardian->update([
                            'fname' => $gFname ?: $existingGuardian->fname,
                            'lname' => $gLname ?: $existingGuardian->lname,
                            'mname' => $gMname ?: $existingGuardian->mname,
                            'phone' => $studentData['guardian_contact'] ?? $existingGuardian->phone,
                            'relation_id' => $studentData['guardian_relation_id'] ?? $existingGuardian->relation_id,
                        ]);
                    } else {
                        \App\Models\Guardian::create([
                            'student_id' => $student_id,
                            'fname' => $gFname ?: 'Guardian',
                            'lname' => $gLname ?: '',
                            'mname' => $gMname ?: '',
                            'phone' => $studentData['guardian_contact'] ?? null,
                            'relation_id' => $studentData['guardian_relation_id'] ?? null,
                        ]);
                    }
                }

                // Generate paid registration invoice & payment log
                if (!empty($student->subscription) && $student->fees_status === 'Paid') {
                    $invoiceService->generateRegistrationInvoiceAndPayment($student, 'MIG_' . $student->student_id . '_' . time());
                }

                // Create/Update SysUser login credentials under 'Student' role
                if ($studentRole && !empty($student->email)) {
                    $emailPrefix = explode('@', $student->email)[0];
                    $lmsUser = $student->lmsuser ?: \Illuminate\Support\Str::slug($emailPrefix, '.');
                    $lmsPass = $student->lmspswd ?: \Illuminate\Support\Str::random(8);
                    
                    $student->update([
                        'lmsuser' => $lmsUser,
                        'lmspswd' => $lmsPass,
                    ]);

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
                        $sysUser->students()->attach($student_id);
                    } else {
                        if (!$existingUser->students()->where('student.student_id', $student_id)->exists()) {
                            $existingUser->students()->attach($student_id);
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $this->forgetBulkUploadData();
            session()->forget([
                'migrated_upload_has_errors',
            ]);

            return redirect()->route('admin.registrations.index')->with('success', count($studentsData) . " students migrated successfully.");
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            Log::error('Migration upload failed: ' . $e->getMessage());
            
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'Integrity constraint violation: 1048 Column') !== false) {
                preg_match("/Column '(.*?)' cannot be null/", $errorMessage, $matches);
                $column = $matches[1] ?? 'unknown';
                $friendlyError = "A database error occurred because a required field ('{$column}') is missing for one or more students in the sheet. Please ensure all required data is provided.";
                return back()->withErrors(['error' => $friendlyError]);
            }
            
            if (strpos($errorMessage, 'Duplicate entry') !== false) {
                preg_match("/Duplicate entry '(.*?)' for key/", $errorMessage, $matches);
                $entry = $matches[1] ?? 'unknown';
                $friendlyError = "A database error occurred because of a duplicate entry: '{$entry}'. This student record might already exist.";
                return back()->withErrors(['error' => $friendlyError]);
            }

            return back()->withErrors(['error' => 'An unexpected error occurred during migration: ' . $e->getMessage()]);
        }
    }

    public function updatePreview(Request $request)
    {
        $students = $this->getBulkUploadData();
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
                
                $lookupResult = $this->getLookupData();
                $lookups = $lookupResult['validation'];

                // Merge the updated data
                $student['data'] = array_merge($student['data'], $updatedData);

                // Set default/blank values for standard if standard is not found or empty
                if (empty($student['data']['std_id'])) {
                    $student['data']['std_id'] = null;
                }

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

                $student['errors'] = $this->validateRow($student['data'], $rowNumber, $lookups, null);
                break;
            }
        }
        unset($student);

        $hasErrors = collect($students)->contains(fn($s) => !empty($s['errors']));

        $this->setBulkUploadData($students);
        session([
            'migrated_upload_has_errors' => $hasErrors,
        ]);

        return response()->json(['success' => true, 'has_errors' => $hasErrors]);
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
                'existing_emails' => Student::selectRaw('LOWER(email) as email, createdby')->get()->pluck('createdby', 'email')->toArray(),
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
