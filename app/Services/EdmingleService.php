<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EdmingleService
{
    protected $baseUrl;
    protected $username;
    protected $password;
    protected $instituteId;
    protected $orgId;
    protected $token = null;

    public function __construct()
    {
        // Load credentials from env or use the provided defaults
        $this->baseUrl = rtrim(env('EDMINGLE_BASE_URL', 'https://bapspramukhacademy-api.edmingle.com/nuSource/api/v1/'), '/') . '/';
        $this->username = env('EDMINGLE_USERNAME', 'www.er.pratik@gmail.com');
        $this->password = env('EDMINGLE_PASSWORD', 'Api@123');
        $this->instituteId = env('EDMINGLE_INSTITUTE_ID', '8032');
        $this->orgId = env('EDMINGLE_ORG_ID', '10193');
    }

    /**
     * Authenticate and retrieve token (apikey) via POST login.
     */
    public function authenticate()
    {
        if ($this->token) {
            return $this->token;
        }

        $url = $this->baseUrl . 'login';
        $payload = [
            'username' => $this->username,
            'password' => $this->password,
            'institute_id' => $this->instituteId,
            'org_id' => $this->orgId
        ];

        try {
            Log::info("Edmingle: Attempting authentication at {$url}");

            // Edmingle expects the payload to be passed as a JSON string inside a form field named 'JSONString'
            $response = Http::asForm()
                ->timeout(10)
                ->post($url, [
                    'JSONString' => json_encode($payload)
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['user']['apikey'] ?? null;

                if ($token) {
                    $this->token = $token;
                    Log::info("Edmingle: Authentication successful.");
                    return $token;
                }
            } else {
                Log::error("Edmingle: Authentication failed with status " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Edmingle Authentication Error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch list of courses (bundles) from bundles/list.
     */
    public function getCourses()
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        $url = $this->baseUrl . 'bundles/list';
        $headers = [
            'ORGID' => (string) $this->orgId,
            'APIKEY' => $token
        ];

        try {
            Log::info("Edmingle: Fetching courses from {$url}");

            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url, [
                    'organization_id' => $this->orgId,
                    'page' => 1,
                    'per_page' => 50,
                    'archived' => 0
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $bundles = $body['bundle'] ?? [];

                if (is_array($bundles)) {
                    $courses = [];
                    foreach ($bundles as $item) {
                        $id = $item['bundle_id'] ?? null;
                        $name = $item['bundle_name'] ?? null;

                        if ($id && $name) {
                            $courses[] = [
                                'id' => $id,
                                'name' => $name,
                                'batches' => [] // Bundles API typically handles internal batches automatically
                            ];
                        }
                    }

                    if (!empty($courses)) {
                        return $courses;
                    }
                }
            } else {
                Log::error("Edmingle: Error fetching courses, status: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error fetching courses: " . $e->getMessage());
        }

        // Return a mock fallback course list for safety/testing if the external API fails or is offline
        Log::warning("Edmingle: Could not fetch live courses. Returning fallback default courses.");
        return [
            [
                'id' => '53017',
                'name' => 'Pramukh SAMPURN - Essential Foundation Course',
                'batches' => []
            ],
            [
                'id' => '53102',
                'name' => 'Pramukh SAMPURN - Advanced General Studies Course',
                'batches' => []
            ]
        ];
    }

    /**
     * Fetch list of master batches for a given course bundle from short/masterbatch.
     */
    public function getBatches($bundleId)
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        $baseUrl = str_replace('https://', 'http://', $this->baseUrl);
        $url = $baseUrl . 'institution/dataexport?type=3&page=1&per_page=200&organization_id=' . $this->orgId;
        $headers = [
            'ORGID' => (string) $this->orgId,
            'apikey' => $token,
            'Content-Type' => 'application/json'
        ];

        try {
            Log::info("Edmingle: Fetching batches for bundle {$bundleId} via dataexport from {$url}");

            $response = Http::withHeaders($headers)
                ->timeout(15)
                ->withBody('', 'application/json')
                ->post($url);

            if ($response->successful()) {
                $body = $response->json();
                $resources = $body['resource'] ?? [];

                $batches = [];
                if (is_array($resources)) {
                    foreach ($resources as $item) {
                        $rowBundleId = $item['bundle_id'] ?? null;
                        $rowBatchId = $item['batch_id'] ?? null;
                        $rowBatchName = $item['batch_name'] ?? null;
                        $rowIsDefaultBatch = $item['is_default_batch'] ?? 0;
                        $rowIsArchived = $item['is_archived'] ?? 0;

                        // Filter batch data based on bundle_id and active status
                        if ($rowBundleId == $bundleId && $rowIsArchived == 0) {
                            $batches[] = [
                                'id' => (string) $rowBatchId,
                                'name' => $rowBatchName,
                                'is_default' => ($rowIsDefaultBatch == 1) ? 1 : 0
                            ];
                        }
                    }
                }
                Log::info("Edmingle: Found " . count($batches) . " active batches matching bundle ID {$bundleId}");
                return $batches;
            } else {
                Log::error("Edmingle: Error fetching batches from dataexport, status: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error fetching batches from dataexport: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Create a new student on Edmingle via organization/students.
     */
    public function createStudent($student)
    {
        $token = $this->authenticate();
        if (!$token) {
            return null;
        }

        // First check if the student already exists by email to prevent duplicate entry
        try {
            $searchUrl = $this->baseUrl . 'student/search';
            Log::info("Edmingle: Checking if student email {$student->email} already exists...");
            $searchResponse = Http::withHeaders([
                'apikey' => $token,
                'ORGID' => (string) $this->orgId
            ])->get($searchUrl, [
                        'student_email' => $student->email,
                        'institution_id' => $this->instituteId
                    ]);

            if ($searchResponse->successful()) {
                $searchData = $searchResponse->json();
                if (!empty($searchData['user_details']) && is_array($searchData['user_details'])) {
                    $existingPortalId = $searchData['user_details'][0]['student_id'] ?? null;
                    if ($existingPortalId) {
                        Log::info("Edmingle: Student already exists with ID: {$existingPortalId}");
                        return $existingPortalId;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error during student existence check: " . $e->getMessage());
        }

        $url = $this->baseUrl . 'organization/students';
        $headers = [
            'ORGID' => (string) $this->orgId,
            'APIKEY' => $token
        ];

        // Generate password if not present
        $password = $student->lmspswd ?: 'Baps@123';
        $fullName = trim("{$student->fname} {$student->mname} {$student->lname}");

        // Build the payload as required by Edmingle's array of 'emails' objects
        $studentData = [
            "contact_number_country_id" => 103, // Defaulting to India (+91)
            "contact_number_dial_code" => "+91",
            "email" => $student->email,
            "name" => $fullName ?: $student->fname,
            "organization_id" => (int) $this->orgId,
            "password" => $password,
            "contact_number" => $student->contact_no,
            "external_uuid" => 'PS-' . (string) $student->student_id // For mapping tracking
        ];

        $payload = [
            'emails' => [$studentData]
        ];

        try {
            Log::info("Edmingle: Creating student at {$url}");

            $response = Http::withHeaders($headers)
                ->asForm()
                ->timeout(60)
                ->post($url, [
                    'JSONString' => json_encode($payload)
                ]);

            if ($response->successful()) {
                $body = $response->json();

                // Edmingle returns 'user_ids', 'accepted_user_ids', or 'accepted_userids' array on successful creation
                $portalId = null;
                if (!empty($body['user_ids']) && is_array($body['user_ids'])) {
                    $portalId = $body['user_ids'][0];
                } elseif (!empty($body['accepted_user_ids']) && is_array($body['accepted_user_ids'])) {
                    $portalId = $body['accepted_user_ids'][0];
                } elseif (!empty($body['accepted_userids']) && is_array($body['accepted_userids'])) {
                    $portalId = $body['accepted_userids'][0];
                }

                if ($portalId) {
                    Log::info("Edmingle: Student created successfully with ID: {$portalId}");
                    return $portalId;
                }

                // Log if accepted_userids or other success keys exist but user_ids is missing
                Log::warning("Edmingle: Create response did not contain user_ids directly.", $body);
                throw new \Exception("Student creation response did not contain student ID: " . json_encode($body));
            } else {
                Log::error("Edmingle: Student creation failed with status: " . $response->status() . " Body: " . $response->body());
                $body = $response->json();
                $msg = $body['message'] ?? 'Unknown error';
                throw new \Exception("Student creation failed: " . $msg);
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error creating student: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if a student exists in Edmingle by email and return their portal ID (student_id).
     */
    public function getStudentByEmail($email)
    {
        $token = $this->authenticate();
        if (!$token) {
            return null;
        }

        try {
            $url = $this->baseUrl . 'student/search';
            Log::info("Edmingle: Checking if student email {$email} already exists...");
            $response = Http::withHeaders([
                'apikey' => $token,
                'ORGID' => (string) $this->orgId
            ])->get($url, [
                        'student_email' => $email,
                        'institution_id' => $this->instituteId
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['user_details']) && is_array($data['user_details'])) {
                    $portalId = $data['user_details'][0]['student_id'] ?? null;
                    if ($portalId) {
                        Log::info("Edmingle: Student found with ID: {$portalId}");
                        return $portalId;
                    }
                }
            } else {
                Log::error("Edmingle: Student search failed, status: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error in getStudentByEmail: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Fetch list of enrolled course bundle IDs for a student.
     */
    public function getStudentEnrolledCourses($portalId)
    {
        $token = $this->authenticate();
        if (!$token) {
            return [];
        }

        $url = $this->baseUrl . "admin/student/enrollcourses/{$portalId}";
        $headers = [
            'ORGID' => (string) $this->orgId,
            'APIKEY' => $token
        ];

        try {
            Log::info("Edmingle: Fetching enrolled courses for student {$portalId} from {$url}");
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($url, [
                    'include_archived_batches' => 0,
                    'include_lastview_info' => 0,
                    'include_expired_courses' => 0,
                    'include_cancelled_courses' => 0
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $batches = $body['batches'] ?? [];

                $enrolledBundleIds = [];
                if (is_array($batches)) {
                    foreach ($batches as $batch) {
                        $bundleId = $batch['bundle_id'] ?? null;
                        if ($bundleId) {
                            $enrolledBundleIds[] = (int) $bundleId;
                        }
                    }
                }
                Log::info("Edmingle: Student {$portalId} is enrolled in bundles: " . implode(', ', $enrolledBundleIds));
                return $enrolledBundleIds;
            } else {
                Log::error("Edmingle: Error fetching enrolled courses, status: " . $response->status() . " Body: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error fetching enrolled courses: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Enroll/Assign student to a course package via admission/walkin/short.
     */
    public function enrollStudent($portalId, $courseId, $batchId = null)
    {
        $token = $this->authenticate();
        if (!$token) {
            return false;
        }

        // Using the simplified walk-in enrollment endpoint
        $url = $this->baseUrl . 'admission/walkin/short';
        $headers = [
            'ORGID' => (string) $this->orgId,
            'APIKEY' => $token
        ];

        $payload = [
            'bundle_id' => (int) $courseId,
            'user_id' => (int) $portalId,
            'organization_id' => (int) $this->orgId
        ];
        try {
            Log::info("Edmingle: Enrolling student ({$portalId}) in bundle ({$courseId}) at {$url}");

            $response = Http::withHeaders($headers)
                ->asForm()
                ->timeout(60)
                ->post($url, [
                    'JSONString' => json_encode($payload)
                ]);

            if ($response->successful()) {
                Log::info("Edmingle: Student enrolled in bundle successfully.");

                // If a batch ID was provided, add the student to the specific batch
                if ($batchId) {
                    $batchUrl = $this->baseUrl . 'students/batches/add';
                    Log::info("Edmingle: Adding student ({$portalId}) to batch ({$batchId}) at {$batchUrl}");

                    $batchPayload = [
                        'student_ids' => [(int) $portalId],
                        'class_ids' => [(int) $batchId],
                        'force_add' => true
                    ];

                    $batchResponse = Http::withHeaders($headers)
                        ->asForm()
                        ->timeout(60)
                        ->post($batchUrl, [
                            'JSONString' => json_encode($batchPayload)
                        ]);

                    if ($batchResponse->successful()) {
                        Log::info("Edmingle: Student added to batch successfully.");
                    } else {
                        Log::error("Edmingle: Batch enrollment failed with status: " . $batchResponse->status() . " Body: " . $batchResponse->body());
                        $body = $batchResponse->json();
                        $msg = $body['message'] ?? 'Unknown error';
                        throw new \Exception("Batch enrollment failed: " . $msg);
                    }
                }

                return true;
            } else {
                Log::error("Edmingle: Enrollment failed with status: " . $response->status() . " Body: " . $response->body());
                $body = $response->json();
                $msg = $body['message'] ?? 'Unknown error';
                throw new \Exception("Enrollment failed: " . $msg);
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error enrolling student: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add student to a specific batch on Edmingle.
     */
    public function addStudentToBatch($portalId, $batchId)
    {
        $token = $this->authenticate();
        if (!$token) {
            return false;
        }

        $headers = [
            'ORGID' => (string) $this->orgId,
            'APIKEY' => $token
        ];

        $batchUrl = $this->baseUrl . 'students/batches/add';
        Log::info("Edmingle: Adding student ({$portalId}) to batch ({$batchId}) at {$batchUrl}");

        $batchPayload = [
            'student_ids' => [(int) $portalId],
            'class_ids' => [(int) $batchId],
            'force_add' => true
        ];

        try {
            $batchResponse = Http::withHeaders($headers)
                ->asForm()
                ->timeout(60)
                ->post($batchUrl, [
                    'JSONString' => json_encode($batchPayload)
                ]);

            if ($batchResponse->successful()) {
                Log::info("Edmingle: Student added to batch successfully.");
                return true;
            } else {
                Log::error("Edmingle: Batch enrollment failed with status: " . $batchResponse->status() . " Body: " . $batchResponse->body());
                $body = $batchResponse->json();
                $msg = $body['message'] ?? 'Unknown error';
                throw new \Exception("Batch enrollment failed: " . $msg);
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error adding student to batch: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bulk add multiple students to a specific batch on Edmingle.
     */
    public function addStudentsToBatch(array $portalIds, $batchId)
    {
        $token = $this->authenticate();
        if (!$token) {
            return false;
        }

        $headers = [
            'ORGID' => (string) $this->orgId,
            'APIKEY' => $token
        ];

        $batchUrl = $this->baseUrl . 'students/batches/add';
        Log::info("Edmingle: Bulk adding students (" . implode(',', $portalIds) . ") to batch ({$batchId}) at {$batchUrl}");

        $batchPayload = [
            'student_ids' => array_map('intval', $portalIds),
            'class_ids' => [(int) $batchId],
            'force_add' => true
        ];

        try {
            $batchResponse = Http::withHeaders($headers)
                ->asForm()
                ->timeout(120)
                ->post($batchUrl, [
                    'JSONString' => json_encode($batchPayload)
                ]);

            if ($batchResponse->successful()) {
                Log::info("Edmingle: Students bulk added to batch successfully.");
                return true;
            } else {
                Log::error("Edmingle: Bulk batch enrollment failed with status: " . $batchResponse->status() . " Body: " . $batchResponse->body());
                $body = $batchResponse->json();
                $msg = $body['message'] ?? 'Unknown error';
                throw new \Exception("Bulk batch enrollment failed: " . $msg);
            }
        } catch (\Exception $e) {
            Log::error("Edmingle: Error in bulk adding students to batch: " . $e->getMessage());
            throw $e;
        }
    }
}
