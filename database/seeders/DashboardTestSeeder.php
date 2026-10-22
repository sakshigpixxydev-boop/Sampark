<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardTestSeeder extends Seeder
{
    public function run(): void
    {
        // Clean up existing test data
        $testUsernames = ['admin', 'initiator', 'school', 'accountant'];
        $existingUsers = DB::table('sysuser')->whereIn('username', $testUsernames)->pluck('sysuser_id');
        
        if ($existingUsers->isNotEmpty()) {
            // Delete related data
            DB::table('userschool')->whereIn('sysuser_id', $existingUsers)->delete();
            DB::table('userstudent')->whereIn('sysuser_id', $existingUsers)->delete();
            DB::table('sysuser')->whereIn('sysuser_id', $existingUsers)->delete();
            $this->command->info('Cleaned up existing test users.');
        }

        // Get role IDs
        $adminRoleId = DB::table('role')->where('rolename', 'Admin')->first()->role_id;
        $initiatorRoleId = DB::table('role')->where('rolename', 'Initiator')->first()->role_id;
        $schoolRoleId = DB::table('role')->where('rolename', 'School')->first()->role_id;
        $accountantRoleId = DB::table('role')->where('rolename', 'Accountant')->first()->role_id;

        // Create test users for each role
        $adminUserId = DB::table('sysuser')->insertGetId([
            'role_id' => $adminRoleId,
            'fname' => 'Admin',
            'lname' => 'User',
            'username' => 'admin',
            'pswd' => Hash::make('password'),
            'pin' => Hash::make('1234'),
            'email' => 'admin@example.com',
            'phone' => '9876543210',
            'inactive' => false,
            'created_at' => now(),
        ]);

        $initiatorUserId = DB::table('sysuser')->insertGetId([
            'role_id' => $initiatorRoleId,
            'fname' => 'Initiator',
            'lname' => 'User',
            'username' => 'initiator',
            'pswd' => Hash::make('password'),
            'pin' => Hash::make('1234'),
            'email' => 'initiator@example.com',
            'phone' => '9876543211',
            'inactive' => false,
            'created_at' => now(),
        ]);

        $schoolUserId = DB::table('sysuser')->insertGetId([
            'role_id' => $schoolRoleId,
            'fname' => 'School',
            'lname' => 'User',
            'username' => 'school',
            'pswd' => Hash::make('password'),
            'pin' => Hash::make('1234'),
            'email' => 'school@example.com',
            'phone' => '9876543212',
            'inactive' => false,
            'created_at' => now(),
        ]);

        $accountantUserId = DB::table('sysuser')->insertGetId([
            'role_id' => $accountantRoleId,
            'fname' => 'Accountant',
            'lname' => 'User',
            'username' => 'accountant',
            'pswd' => Hash::make('password'),
            'pin' => Hash::make('1234'),
            'email' => 'accountant@example.com',
            'phone' => '9876543213',
            'inactive' => false,
            'created_at' => now(),
        ]);

        // Get necessary IDs for school creation
        $schoolTypeId = DB::table('schooltype')->first()->schooltype_id ?? null;
        $eduboardId = DB::table('ms_eductionalboard')->first()->eduboard_id ?? null;
        $stateId = DB::table('ms_state')->first()->state_id ?? null;
        
        if (!$schoolTypeId || !$eduboardId || !$stateId) {
            $this->command->warn('Missing master data. Please run MasterDataSeeder first.');
            return;
        }
        
        // Create a district
        $distId = DB::table('ms_dist')->insertGetId([
            'state_id' => $stateId,
            'dname' => 'Ahmedabad',
            'created_at' => now(),
        ]);
        
        // Create a taluka
        $talukaId = DB::table('ms_taluko')->insertGetId([
            'dist_id' => $distId,
            'tname' => 'Ahmedabad City',
            'created_at' => now(),
        ]);

        // Create test schools
        $school1Id = DB::table('school')->insertGetId([
            'schooltype_id' => $schoolTypeId,
            'eduboard_id' => $eduboardId,
            'schoolname' => 'Sunshine Public School',
            'takula_id' => $talukaId,
            'address1' => '123 Main Street',
            'city_village' => 'Ahmedabad',
            'pincode' => '380001',
            'phone' => '079-12345678',
            'email' => 'sunshine@school.com',
            'adminname' => 'School Admin 1',
            'admincontact' => '9876543220',
            'inactive' => false,
            'status' => 1,
            'created_at' => now(),
        ]);

        $school2Id = DB::table('school')->insertGetId([
            'schooltype_id' => $schoolTypeId,
            'eduboard_id' => $eduboardId,
            'schoolname' => 'Green Valley School',
            'takula_id' => $talukaId,
            'address1' => '456 Park Avenue',
            'city_village' => 'Ahmedabad',
            'pincode' => '380002',
            'phone' => '079-87654321',
            'email' => 'greenvalley@school.com',
            'adminname' => 'School Admin 2',
            'admincontact' => '9876543221',
            'inactive' => false,
            'status' => 1,
            'created_at' => now(),
        ]);

        // Assign schools to initiator and school user
        DB::table('userschool')->insert([
            ['sysuser_id' => $initiatorUserId, 'school_id' => $school1Id],
            ['sysuser_id' => $initiatorUserId, 'school_id' => $school2Id],
            ['sysuser_id' => $schoolUserId, 'school_id' => $school1Id],
        ]);

        // Get student type and standard
        $studentTypeId = DB::table('studenttype')->first()->studenttype_id ?? null;
        $standardId = DB::table('ms_standard')->first()->std_id ?? null;

        if (!$studentTypeId || !$standardId) {
            $this->command->warn('Missing student type or standard data.');
            return;
        }

        // Create test students
        $students = [];
        for ($i = 1; $i <= 10; $i++) {
            $schoolId = $i <= 5 ? $school1Id : $school2Id;
            $studentId = DB::table('student')->insertGetId([
                'studenttype_id' => $studentTypeId,
                'eduboard_id' => $eduboardId,
                'std_id' => $standardId,
                'school_id' => $schoolId,
                'takula_id' => $talukaId,
                'fname' => 'Student' . $i,
                'mname' => 'Test',
                'lname' => 'User',
                'gender' => $i % 2 == 0 ? 'M' : 'F',
                'email' => 'student' . $i . '@example.com',
                'birthdate' => now()->subYears(15)->format('Y-m-d'),
                'address' => 'Address ' . $i,
                'city_village' => 'Ahmedabad',
                'pincode' => '380001',
                'contact_no' => '987654' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'whatsapp_no' => '987654' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'educationmedium' => '3', // 3 = English
                'institute' => 'Test Institute',
            ]);
            $students[] = $studentId;
        }

        // Assign students to initiator
        foreach ($students as $studentId) {
            DB::table('userstudent')->insert([
                'sysuser_id' => $initiatorUserId,
                'student_id' => $studentId,
            ]);
        }

        // Create semester batch
        $semBatchId = DB::table('sembatches')->insertGetId([
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'capacity' => 100,
            'fees' => 5000.00,
            'code_prefix' => 'SEM2025',
            'status' => 0,
            'created_at' => now(),
        ]);

        // Create invoices for students
        foreach ($students as $index => $studentId) {
            $student = DB::table('student')->where('student_id', $studentId)->first();
            
            $invoiceId = DB::table('invoice')->insertGetId([
                'invoice_date' => now()->subDays(rand(1, 30)),
                'due_date' => now()->addDays(rand(10, 30)),
                'notes' => 'Test invoice for student ' . ($index + 1),
                'total_before_discount' => 5000.00,
                'discount_total' => 0.00,
                'total_due' => 5000.00,
                'status' => $index % 3 == 0 ? 'ISSUED' : ($index % 3 == 1 ? 'PARTIAL' : 'PAID'),
                'school_id' => $student->school_id,
                'student_id' => $studentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create invoice items
            DB::table('invoice_item')->insert([
                'invoice_id' => $invoiceId,
                'description' => 'Semester Fees',
                'qty' => 1,
                'unit_amount' => 5000.00,
                'line_amount' => 5000.00,
                'fee_plan_id' => null,
            ]);

            // Create payment logs for some invoices
            if ($index % 2 == 0) {
                $paymentModeId = DB::table('paymentmode')->first()->paymode_id ?? null;
                if ($paymentModeId) {
                    DB::table('paymentlog')->insert([
                        'paymode_id' => $paymentModeId,
                        'processdatetime' => now()->subDays(rand(1, 20)),
                        'amount' => $index % 3 == 1 ? 2500.00 : 5000.00,
                        'status' => $index % 4 == 0 ? '0' : ($index % 4 == 1 ? '1' : ($index % 4 == 2 ? '3' : '1')), // 0=PENDING, 1=AUTHORIZED, 3=FAILED
                        'txnid' => 'TXN' . time() . $index,
                        'orgresponse' => 'Test payment response',
                        'discount' => 0.00,
                        'invoice_id' => $invoiceId,
                    ]);
                }
            }
        }

        $this->command->info('Dashboard test data created successfully!');
        $this->command->info('Test Users:');
        $this->command->info('  Admin: username=admin, password=password, pin=1234');
        $this->command->info('  Initiator: username=initiator, password=password, pin=1234');
        $this->command->info('  School: username=school, password=password, pin=1234');
        $this->command->info('  Accountant: username=accountant, password=password, pin=1234');
    }
}
