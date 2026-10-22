<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use App\Models\SemesterBatch;
use App\Models\UploadBatch;
use App\Models\PendingStudent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StudentBulkUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        Storage::fake('local');
    }

    public function test_authenticated_user_can_view_bulk_upload_page()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('students.bulk-upload'));
        
        $response->assertStatus(200);
        $response->assertSee('Bulk Upload Students');
    }

    public function test_csv_upload_validates_file_type()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $batch = SemesterBatch::factory()->create();
        
        $this->actingAs($user);
        
        $file = UploadedFile::fake()->create('students.txt', 100);
        
        $response = $this->post(route('students.bulk-upload.process'), [
            'csv_file' => $file,
            'sembatches_id' => $batch->sembatches_id,
        ]);
        
        $response->assertSessionHasErrors('csv_file');
    }

    public function test_csv_upload_validates_semester_batch_required()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $file = UploadedFile::fake()->create('students.csv', 100, 'text/csv');
        
        $response = $this->post(route('students.bulk-upload.process'), [
            'csv_file' => $file,
        ]);
        
        $response->assertSessionHasErrors('sembatches_id');
    }

    public function test_upload_batch_can_be_viewed()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $batch = SemesterBatch::factory()->create();
        
        $uploadBatch = UploadBatch::create([
            'batch_code' => 'TEST-' . time(),
            'sembatches_id' => $batch->sembatches_id,
            'uploaded_by' => $user->sysuser_id,
            'upload_date' => now(),
            'total_students' => 5,
            'fee_per_student' => 1000.00,
            'total_fees' => 5000.00,
            'status' => 'pending',
            'payment_verified' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('accountant.batches.show', $uploadBatch->batch_id));
        
        $response->assertStatus(200);
        $response->assertSee($uploadBatch->batch_code);
    }

    public function test_upload_batch_displays_correct_totals()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $batch = SemesterBatch::factory()->create();
        
        $uploadBatch = UploadBatch::create([
            'batch_code' => 'TEST-' . time(),
            'sembatches_id' => $batch->sembatches_id,
            'uploaded_by' => $user->sysuser_id,
            'upload_date' => now(),
            'total_students' => 10,
            'fee_per_student' => 1500.00,
            'total_fees' => 15000.00,
            'status' => 'pending',
            'payment_verified' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('accountant.batches.show', $uploadBatch->batch_id));
        
        $response->assertStatus(200);
        $response->assertSee('10');
        $response->assertSee('1,500.00');
        $response->assertSee('15,000.00');
    }

    public function test_pending_batch_can_be_approved()
    {
        $role = Role::where('rolename', 'Accountant')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Accountant',
            'username' => 'testaccountant',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $batch = SemesterBatch::factory()->create();
        
        $uploadBatch = UploadBatch::create([
            'batch_code' => 'TEST-' . time(),
            'sembatches_id' => $batch->sembatches_id,
            'uploaded_by' => $user->sysuser_id,
            'upload_date' => now(),
            'total_students' => 5,
            'fee_per_student' => 1000.00,
            'total_fees' => 5000.00,
            'status' => 'pending',
            'payment_verified' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('accountant.batches.approve-form', $uploadBatch->batch_id));
        
        $response->assertStatus(200);
        $response->assertSee('Approve Payment');
    }

    public function test_pending_batch_can_be_rejected()
    {
        $role = Role::where('rolename', 'Accountant')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Accountant',
            'username' => 'testaccountant',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $batch = SemesterBatch::factory()->create();
        
        $uploadBatch = UploadBatch::create([
            'batch_code' => 'TEST-' . time(),
            'sembatches_id' => $batch->sembatches_id,
            'uploaded_by' => $user->sysuser_id,
            'upload_date' => now(),
            'total_students' => 5,
            'fee_per_student' => 1000.00,
            'total_fees' => 5000.00,
            'status' => 'pending',
            'payment_verified' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('accountant.batches.reject-form', $uploadBatch->batch_id));
        
        $response->assertStatus(200);
        $response->assertSee('Reject Batch');
    }
}
