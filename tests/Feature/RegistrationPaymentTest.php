<?php

namespace Tests\Feature;

use App\Models\FeePlan;
use App\Models\PaymentMode;
use App\Models\Student;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class RegistrationPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        PaymentMode::create(['paymodename' => 'Online']);
    }

    private function registrationInProgress(): array
    {
        $student = Student::create([
            'studenttype_id' => 1,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => DB::table('ms_taluko')->value('takula_id'),
            'fname' => 'Test',
            'lname' => 'Student',
            'email' => 'registration-payment@example.com',
            'fees_status' => 'In Progress',
            'subscription' => 'Annual Plan',
            'razorpay_order_id' => 'order_registration_123',
        ]);

        $plan = FeePlan::create([
            'fee_name' => 'Annual Plan',
            'amount' => 2500,
            'valid_from' => now()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
            'is_active_on_form' => 1,
        ]);

        return [$student, $plan, base64_encode((string) $student->student_id)];
    }

    public function test_confirmed_payment_is_saved_once_with_invoice_and_payment_log(): void
    {
        [$student, $plan, $encodedId] = $this->registrationInProgress();
        $service = Mockery::mock(RazorpayService::class);
        $service->shouldReceive('confirmPayment')
            ->once()
            ->with('order_registration_123', 'pay_registration_123', 'valid_signature', 250000)
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_registration_123',
                    'order_id' => 'order_registration_123',
                    'amount' => 250000,
                    'currency' => 'INR',
                    'status' => 'captured',
                ],
            ]);
        $this->instance(RazorpayService::class, $service);

        $callback = [
            'student_id' => $encodedId,
            'plan_id' => $plan->fee_plan_id,
            'razorpay_order_id' => 'order_registration_123',
            'razorpay_payment_id' => 'pay_registration_123',
            'razorpay_signature' => 'valid_signature',
        ];

        $this->get(route('registration.payment-status', $callback));
        $this->get(route('registration.payment-status', $callback))
            ->assertRedirect(route('documents.form', $encodedId));

        $this->assertDatabaseHas('student', [
            'student_id' => $student->student_id,
            'fees_status' => 'Paid',
            'razorpay_payment_id' => 'pay_registration_123',
        ]);
        $this->assertSame(1, DB::table('invoice')->where('student_id', $student->student_id)->count());
        $this->assertSame(1, DB::table('paymentlog')->where('txnid', 'pay_registration_123')->count());
        $this->assertSame('1', (string) DB::table('paymentlog')->where('txnid', 'pay_registration_123')->value('status'));
    }

    public function test_unconfirmed_callback_never_marks_student_paid_or_creates_payment_records(): void
    {
        [$student, $plan, $encodedId] = $this->registrationInProgress();
        $service = Mockery::mock(RazorpayService::class);
        $service->shouldReceive('confirmPayment')
            ->once()
            ->andReturn(['success' => false, 'error' => 'Invalid signature.']);
        $this->instance(RazorpayService::class, $service);

        $this->get(route('registration.payment-status', [
            'student_id' => $encodedId,
            'plan_id' => $plan->fee_plan_id,
            'razorpay_order_id' => 'order_registration_123',
            'razorpay_payment_id' => 'pay_forged_123',
            'razorpay_signature' => 'invalid',
        ]))->assertRedirect(route('registration.payment', [$encodedId, $plan->fee_plan_id]));

        $this->assertDatabaseHas('student', [
            'student_id' => $student->student_id,
            'fees_status' => 'In Progress',
            'razorpay_payment_id' => null,
        ]);
        $this->assertDatabaseCount('invoice', 0);
        $this->assertDatabaseCount('paymentlog', 0);
    }

    public function test_interrupted_payment_keeps_registration_and_resets_payment_status(): void
    {
        [$student, $plan, $encodedId] = $this->registrationInProgress();
        $this->instance(RazorpayService::class, Mockery::mock(RazorpayService::class));

        $this->post(route('registration.payment-status.update'), [
            'student_id' => $encodedId,
            'plan_id' => $plan->fee_plan_id,
            'razorpay_order_id' => 'order_registration_123',
            'status' => 'interrupted',
        ])->assertNoContent();

        $this->assertDatabaseHas('student', [
            'student_id' => $student->student_id,
            'fees_status' => 'Pending',
            'subscription' => 'Annual Plan',
            'razorpay_order_id' => 'order_registration_123',
        ]);
        $this->assertDatabaseCount('invoice', 0);
        $this->assertDatabaseCount('paymentlog', 0);
    }
}
