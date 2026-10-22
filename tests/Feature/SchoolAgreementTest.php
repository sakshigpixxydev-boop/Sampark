<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolType;
use App\Models\EducationalBoard;
use App\Models\Taluko;
use App\Models\AgreementTemplate;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SchoolAgreementTest extends TestCase
{
    use DatabaseTransactions;

    protected $adminUser;
    protected $coordinatorUser;
    protected $initiatorUser;
    protected $otherInitiatorUser;
    protected $schoolAdminUser;
    protected $school;

    protected function setUp(): void
    {
        parent::setUp();

        // Fetch or create Roles
        $adminRole = Role::firstOrCreate(['rolename' => 'Admin']);
        $coordinatorRole = Role::firstOrCreate(['rolename' => 'Sankalp Coordinator (School)']);
        $initiatorRole = Role::firstOrCreate(['rolename' => 'Initiator']);
        $schoolRole = Role::firstOrCreate(['rolename' => 'School']);

        // Create Users
        $this->adminUser = SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'System',
            'lname' => 'Admin',
            'username' => 'testadmin_' . uniqid(),
            'email' => 'admin_' . uniqid() . '@test.com',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);

        $this->coordinatorUser = SysUser::create([
            'role_id' => $coordinatorRole->role_id,
            'fname' => 'Sankalp',
            'lname' => 'Coordinator',
            'username' => 'testcoordinator_' . uniqid(),
            'email' => 'coordinator_' . uniqid() . '@test.com',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);

        $this->initiatorUser = SysUser::create([
            'role_id' => $initiatorRole->role_id,
            'fname' => 'Lead',
            'lname' => 'Initiator',
            'username' => 'testinitiator_' . uniqid(),
            'email' => 'initiator_' . uniqid() . '@test.com',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);

        $this->otherInitiatorUser = SysUser::create([
            'role_id' => $initiatorRole->role_id,
            'fname' => 'Other',
            'lname' => 'Initiator',
            'username' => 'otherinitiator_' . uniqid(),
            'email' => 'otherinitiator_' . uniqid() . '@test.com',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);

        $this->schoolAdminUser = SysUser::create([
            'role_id' => $schoolRole->role_id,
            'fname' => 'School',
            'lname' => 'Admin',
            'username' => 'testschooladmin_' . uniqid(),
            'email' => 'schooladmin_' . uniqid() . '@test.com',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);

        // Get required master data
        $schoolType = SchoolType::first() ?? SchoolType::create(['schooltypename' => 'Secondary']);
        $board = EducationalBoard::first() ?? EducationalBoard::create(['boardname' => 'GSEB']);
        $taluka = Taluko::first() ?? Taluko::create(['tname' => 'City', 'dist_id' => 1]);

        // Create School & assign to initiator & school admin
        $this->school = School::create([
            'schooltype_id' => $schoolType->schooltype_id,
            'eduboard_id' => $board->eduboard_id,
            'takula_id' => $taluka->takula_id,
            'schoolname' => 'Test Agreement School',
            'adminname' => 'John Principal',
            'admincontact' => '9876543210',
            'email' => 'testschool@example.com',
            'initiator_id' => $this->initiatorUser->sysuser_id,
            'agreement_status' => 'pending',
            'status' => 1,
            'inactive' => false
        ]);

        // Associate school admin user with school via pivot
        $this->schoolAdminUser->schools()->attach($this->school->school_id);

        // Ensure active agreement template exists
        AgreementTemplate::updateOrCreate(
            ['slug' => 'school_registration'],
            [
                'title' => 'School Terms & Conditions',
                'content_en' => '<p>Verify accuracy of student data.</p>',
                'is_active' => true
            ]
        );
    }

    /**
     * Test agreement access controls
     */
    public function test_access_authorization(): void
    {
        // 1. Admin can access
        $response = $this->actingAs($this->adminUser)->get(route('schools.agreement.show', $this->school->school_id));
        $response->assertStatus(200);

        // 2. Coordinator can access
        $response = $this->actingAs($this->coordinatorUser)->get(route('schools.agreement.show', $this->school->school_id));
        $response->assertStatus(200);

        // 3. Assigned Initiator can access
        $response = $this->actingAs($this->initiatorUser)->get(route('schools.agreement.show', $this->school->school_id));
        $response->assertStatus(200);

        // 4. Assigned School Admin can access
        $response = $this->actingAs($this->schoolAdminUser)->get(route('schools.agreement.show', $this->school->school_id));
        $response->assertStatus(200);

        // 5. Unassigned Initiator is aborted 403
        $response = $this->actingAs($this->otherInitiatorUser)->get(route('schools.agreement.show', $this->school->school_id));
        $response->assertStatus(403);
    }

    /**
     * Test dynamic Word document download
     */
    public function test_download_sample_agreement(): void
    {
        $response = $this->actingAs($this->initiatorUser)->get(route('schools.agreement.download-sample', $this->school->school_id));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-word');
        $response->assertHeader('Content-Disposition', 'attachment; filename="sample_agreement_' . str_replace(' ', '_', strtolower($this->school->schoolname)) . '.doc"');
        $response->assertSee('Verify accuracy of student data.');
        $response->assertSee($this->school->schoolname);
    }

    /**
     * Test full approval workflow transitions and constraints
     */
    public function test_full_agreement_workflow(): void
    {
        Storage::fake('public');

        // Step 1: Initiator uploads signed agreement PDF
        $file = UploadedFile::fake()->create('agreement.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->initiatorUser)->post(route('schools.agreement.upload', $this->school->school_id), [
            'agreement_pdf' => $file
        ]);

        $response->assertRedirect();
        $this->school->refresh();
        $this->assertEquals('uploaded', $this->school->agreement_status);
        $this->assertNotNull($this->school->agreement_pdf_path);
        Storage::disk('public')->assertExists($this->school->agreement_pdf_path);

        // Assert AuditLog recorded the upload
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'AGREEMENT_UPLOADED',
            'auditable_type' => School::class,
            'auditable_id' => $this->school->school_id,
            'user_id' => $this->initiatorUser->sysuser_id
        ]);

        // Step 2: Initiator approves the agreement
        $response = $this->actingAs($this->initiatorUser)->post(route('schools.agreement.approve-initiator', $this->school->school_id));
        
        $response->assertRedirect();
        $this->school->refresh();
        $this->assertEquals('initiator_approved', $this->school->agreement_status);

        // Assert AuditLog recorded the initiator approval
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'INITIATOR_APPROVED',
            'auditable_type' => School::class,
            'auditable_id' => $this->school->school_id,
            'user_id' => $this->initiatorUser->sysuser_id
        ]);

        // Step 3: School Coordinator reviews & approves the agreement
        $response = $this->actingAs($this->coordinatorUser)->post(route('schools.agreement.approve-coordinator', $this->school->school_id));

        $response->assertRedirect();
        $this->school->refresh();
        $this->assertEquals('coordinator_approved', $this->school->agreement_status);

        // Assert AuditLog recorded coordinator approval
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'COORDINATOR_APPROVED',
            'auditable_type' => School::class,
            'auditable_id' => $this->school->school_id,
            'user_id' => $this->coordinatorUser->sysuser_id
        ]);

        // Step 4: Verify read-only data constraints once approved by Coordinator
        $anotherFile = UploadedFile::fake()->create('replaced.pdf', 1000, 'application/pdf');
        $response = $this->actingAs($this->initiatorUser)->post(route('schools.agreement.upload', $this->school->school_id), [
            'agreement_pdf' => $anotherFile
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'The agreement has been finalized and is read-only.');
    }

    /**
     * Test signature image upload and change audit logging
     */
    public function test_signature_upload_and_audit(): void
    {
        Storage::fake('public');

        $sigImage1 = UploadedFile::fake()->image('signature1.png', 100, 50);

        // 1. Upload signature
        $response = $this->actingAs($this->initiatorUser)->post(route('schools.agreement.upload-signature', $this->school->school_id), [
            'signature_image' => $sigImage1
        ]);

        $response->assertRedirect();
        $this->initiatorUser->refresh();
        $this->assertNotNull($this->initiatorUser->signature_path);
        Storage::disk('public')->assertExists($this->initiatorUser->signature_path);

        // Assert AuditLog recorded signature uploaded
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'SIGNATURE_UPLOADED',
            'auditable_type' => School::class,
            'auditable_id' => $this->school->school_id,
            'user_id' => $this->initiatorUser->sysuser_id
        ]);

        // 2. Change signature
        $sigImage2 = UploadedFile::fake()->image('signature2.jpg', 120, 60);
        $response = $this->actingAs($this->initiatorUser)->post(route('schools.agreement.upload-signature', $this->school->school_id), [
            'signature_image' => $sigImage2
        ]);

        $response->assertRedirect();
        $this->initiatorUser->refresh();
        Storage::disk('public')->assertExists($this->initiatorUser->signature_path);

        // Assert AuditLog recorded signature changed
        $this->assertDatabaseHas('audit_logs', [
            'event_type' => 'SIGNATURE_CHANGED',
            'auditable_type' => School::class,
            'auditable_id' => $this->school->school_id,
            'user_id' => $this->initiatorUser->sysuser_id
        ]);
    }
}
