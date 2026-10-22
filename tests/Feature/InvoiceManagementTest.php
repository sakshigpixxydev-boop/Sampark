<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Student;
use App\Models\School;
use App\Models\FeePlan;
use App\Models\DiscountRule;
use App\Models\SemesterBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_authenticated_user_can_view_invoices_list()
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
        
        $response = $this->get(route('invoices.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Invoices');
    }

    public function test_invoice_generation_calculates_fees_correctly()
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
        
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 500,
            'valid_from' => now()->subDay()
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post(route('invoices.store'), [
            'student_id' => $student->student_id,
            'sembatches_id' => $batch->sembatches_id,
        ]);
        
        $response->assertRedirect();
        
        $invoice = Invoice::where('student_id', $student->student_id)->first();
        $this->assertEquals(1500, $invoice->total_before_discount);
    }

    public function test_invoice_applies_flat_discount()
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
        
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        DiscountRule::factory()->create([
            'scope' => 'SCHOOL',
            'school_id' => $school->school_id,
            'discount_type' => 'FLAT',
            'discount_value' => 200,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post(route('invoices.store'), [
            'student_id' => $student->student_id,
            'sembatches_id' => $batch->sembatches_id,
        ]);
        
        $invoice = Invoice::where('student_id', $student->student_id)->first();
        $this->assertEquals(1000, $invoice->total_before_discount);
        $this->assertEquals(200, $invoice->discount_total);
        $this->assertEquals(800, $invoice->total_due);
    }

    public function test_invoice_applies_percent_discount()
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
        
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        DiscountRule::factory()->create([
            'scope' => 'SCHOOL',
            'school_id' => $school->school_id,
            'discount_type' => 'PERCENT',
            'discount_value' => 15,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post(route('invoices.store'), [
            'student_id' => $student->student_id,
            'sembatches_id' => $batch->sembatches_id,
        ]);
        
        $invoice = Invoice::where('student_id', $student->student_id)->first();
        $this->assertEquals(1000, $invoice->total_before_discount);
        $this->assertEquals(150, $invoice->discount_total);
        $this->assertEquals(850, $invoice->total_due);
    }

    public function test_invoice_status_can_be_updated()
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
        
        $invoice = Invoice::factory()->create(['status' => 'DRAFT']);
        
        $this->actingAs($user);
        
        $response = $this->put(route('invoices.update', $invoice->invoice_id), [
            'status' => 'ISSUED',
        ]);
        
        $response->assertRedirect();
        
        $invoice->refresh();
        $this->assertEquals('ISSUED', $invoice->status);
    }

    public function test_invoice_can_be_voided()
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
        
        $invoice = Invoice::factory()->create(['status' => 'ISSUED']);
        
        $this->actingAs($user);
        
        $response = $this->put(route('invoices.update', $invoice->invoice_id), [
            'status' => 'VOID',
        ]);
        
        $invoice->refresh();
        $this->assertEquals('VOID', $invoice->status);
    }

    public function test_invoice_details_can_be_viewed()
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
        
        $invoice = Invoice::factory()->create();
        InvoiceItem::factory()->create(['invoice_id' => $invoice->invoice_id]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('invoices.show', $invoice->invoice_id));
        
        $response->assertStatus(200);
        $response->assertSee('Invoice Details');
    }
}
