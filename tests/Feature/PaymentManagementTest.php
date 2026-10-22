<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use App\Models\PaymentLog;
use App\Models\PaymentMode;
use App\Models\Student;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_authenticated_user_can_view_payments_list()
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
        
        $response = $this->get(route('payments.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Payment Logs');
    }

    public function test_payment_can_be_recorded()
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
        
        $student = Student::factory()->create();
        $invoice = Invoice::factory()->create(['student_id' => $student->student_id]);
        $paymentMode = PaymentMode::first();
        
        $this->actingAs($user);
        
        $response = $this->post(route('payments.store'), [
            'student_id' => $student->student_id,
            'invoice_id' => $invoice->invoice_id,
            'paymentmode_id' => $paymentMode->paymentmode_id,
            'payment_datetime' => now()->format('Y-m-d H:i:s'),
            'transaction_id' => 'TXN-' . time(),
            'amount' => 1000.00,
            'status' => 'PENDING',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('paymentlog', [
            'student_id' => $student->student_id,
            'amount' => 1000.00,
        ]);
    }

    public function test_payment_validates_required_fields()
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
        
        $response = $this->post(route('payments.store'), []);
        
        $response->assertSessionHasErrors([
            'student_id',
            'paymentmode_id',
            'payment_datetime',
            'transaction_id',
        ]);
    }

    public function test_payment_status_can_be_updated()
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
        
        $payment = PaymentLog::factory()->create(['status' => 'PENDING']);
        
        $this->actingAs($user);
        
        $response = $this->put(route('payments.update', $payment->paymentlog_id), [
            'status' => 'AUTHORIZED',
            'student_id' => $payment->student_id,
            'paymentmode_id' => $payment->paymentmode_id,
            'payment_datetime' => $payment->payment_datetime,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
        
        $response->assertRedirect();
        
        $payment->refresh();
        $this->assertEquals('AUTHORIZED', $payment->status);
    }

    public function test_payment_can_transition_to_paid()
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
        
        $payment = PaymentLog::factory()->create(['status' => 'AUTHORIZED']);
        
        $this->actingAs($user);
        
        $response = $this->put(route('payments.update', $payment->paymentlog_id), [
            'status' => 'PAID',
            'student_id' => $payment->student_id,
            'paymentmode_id' => $payment->paymentmode_id,
            'payment_datetime' => $payment->payment_datetime,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
        
        $payment->refresh();
        $this->assertEquals('PAID', $payment->status);
    }

    public function test_payment_can_be_refunded()
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
        
        $payment = PaymentLog::factory()->create(['status' => 'PAID']);
        
        $this->actingAs($user);
        
        $response = $this->put(route('payments.update', $payment->paymentlog_id), [
            'status' => 'REFUNDED',
            'student_id' => $payment->student_id,
            'paymentmode_id' => $payment->paymentmode_id,
            'payment_datetime' => $payment->payment_datetime,
            'transaction_id' => $payment->transaction_id,
            'amount' => $payment->amount,
        ]);
        
        $payment->refresh();
        $this->assertEquals('REFUNDED', $payment->status);
    }

    public function test_payment_details_can_be_viewed()
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
        
        $payment = PaymentLog::factory()->create();
        
        $this->actingAs($user);
        
        $response = $this->get(route('payments.show', $payment->paymentlog_id));
        
        $response->assertStatus(200);
        $response->assertSee('Payment Details');
    }

    public function test_payments_can_be_filtered_by_status()
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
        
        PaymentLog::factory()->create(['status' => 'PENDING']);
        PaymentLog::factory()->create(['status' => 'PAID']);
        
        $this->actingAs($user);
        
        $response = $this->get(route('payments.index', ['status' => 'PENDING']));
        
        $response->assertStatus(200);
    }
}
