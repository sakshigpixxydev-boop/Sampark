<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Student;
use App\Models\ProcessedRazorpayEvent;
use App\Models\UploadBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected $webhookSecret = 'test_webhook_secret';

    protected function setUp(): void
    {
        parent::setUp();

        // Configure test secret and credentials
        config([
            'razorpay.webhook_secret' => $this->webhookSecret,
            'razorpay.key_id' => 'rzp_test_key_id',
            'razorpay.key_secret' => 'rzp_test_key_secret',
        ]);

        // Insert required seed records for student foreign keys in sqlite
        DB::table('studenttype')->updateOrInsert([
            'studenttype_id' => 2,
        ], [
            'studenttypename' => 'Non-BAPS',
        ]);
        DB::table('ms_eductionalboard')->updateOrInsert([
            'eduboard_id' => 1,
        ], [
            'boardname' => 'GSEB',
        ]);
        DB::table('ms_standard')->updateOrInsert([
            'std_id' => 1,
        ], [
            'stdname' => 'Standard 6',
        ]);
        DB::table('ms_country')->updateOrInsert([
            'country_id' => 1,
        ], [
            'cname' => 'India',
        ]);
        DB::table('ms_state')->updateOrInsert([
            'state_id' => 1,
        ], [
            'sname' => 'Gujarat',
            'country_id' => 1,
        ]);
        DB::table('ms_dist')->updateOrInsert([
            'dist_id' => 1,
        ], [
            'dname' => 'Ahmedabad',
            'state_id' => 1,
        ]);
        DB::table('ms_taluko')->updateOrInsert([
            'takula_id' => 1,
        ], [
            'tname' => 'Ghatlodia',
            'dist_id' => 1,
        ]);
    }

    protected function generateSignature($payload)
    {
        return hash_hmac('sha256', $payload, $this->webhookSecret);
    }

    /**
     * Helper to create a mock order implementing ArrayAccess
     */
    protected function createMockOrder($status, $paymentId = null, $orderId = null)
    {
        return new class($status, $paymentId, $orderId) implements \ArrayAccess {
            public $status;
            public $paymentId;
            public $id;

            public function __construct($status, $paymentId, $orderId)
            {
                $this->status = $status;
                $this->paymentId = $paymentId;
                $this->id = $orderId;
            }

            public function payments()
            {
                $items = [];
                if ($this->paymentId) {
                    $items[] = (object)['id' => $this->paymentId];
                }
                return (object)[
                    'items' => $items
                ];
            }

            public function offsetExists($offset): bool
            {
                return in_array($offset, ['status', 'id']);
            }

            public function offsetGet($offset): mixed
            {
                if ($offset === 'status') {
                    return $this->status;
                }
                if ($offset === 'id') {
                    return $this->id;
                }
                return null;
            }

            public function offsetSet($offset, $value): void {}
            public function offsetUnset($offset): void {}
        };
    }

    public function test_webhook_returns_400_for_missing_signature()
    {
        $payload = json_encode(['id' => 'evt_123']);
        $response = $this->postJson(route('razorpay.webhook'), json_decode($payload, true));
        $response->assertStatus(400);
    }

    public function test_webhook_returns_400_for_invalid_signature()
    {
        $payload = json_encode(['id' => 'evt_123']);
        $response = $this->postJson(
            route('razorpay.webhook'),
            json_decode($payload, true),
            ['X-Razorpay-Signature' => 'invalid_sig']
        );
        $response->assertStatus(400);
    }

    public function test_webhook_processes_student_payment_successfully()
    {
        // 1. Create a student with pending fees status
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'John',
            'mname' => 'A',
            'lname' => 'Doe',
            'email' => 'john.doe@example.com',
            'fees_status' => 'Pending',
        ]);

        // 2. Mock payment.captured event payload
        $eventId = 'evt_test_payment_123';
        $paymentId = 'pay_test_payment_123';
        $orderId = 'order_test_payment_123';

        $payloadData = [
            'id' => $eventId,
            'entity' => 'event',
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => $orderId,
                        'notes' => [
                            'student_id' => $student->student_id,
                            'plan_id' => 1,
                        ]
                    ]
                ]
            ]
        ];

        $rawBody = json_encode($payloadData);
        $signature = $this->generateSignature($rawBody);

        // Send request
        $response = $this->postJson(
            route('razorpay.webhook'),
            $payloadData,
            ['X-Razorpay-Signature' => $signature]
        );

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'result' => 'processed']);

        // Verify student fees status updated to Paid and enrollment number generated
        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertNotEmpty($student->enrolment_no);

        // Verify event logged in DB
        $this->assertDatabaseHas('processed_razorpay_events', [
            'event_id' => $eventId,
            'payment_id' => $paymentId,
            'order_id' => $orderId,
            'status' => 'processed',
        ]);
    }

    public function test_webhook_idempotency_prevents_duplicate_processing_for_same_event_id()
    {
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Jane',
            'mname' => 'B',
            'lname' => 'Doe',
            'email' => 'jane.doe@example.com',
            'fees_status' => 'Pending',
        ]);

        $eventId = 'evt_duplicate_event_123';
        $paymentId = 'pay_duplicate_payment_123';
        $orderId = 'order_duplicate_order_123';

        $payloadData = [
            'id' => $eventId,
            'entity' => 'event',
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => $orderId,
                        'notes' => [
                            'student_id' => $student->student_id,
                            'plan_id' => 1,
                        ]
                    ]
                ]
            ]
        ];

        $rawBody = json_encode($payloadData);
        $signature = $this->generateSignature($rawBody);

        // Send first request
        $response1 = $this->postJson(
            route('razorpay.webhook'),
            $payloadData,
            ['X-Razorpay-Signature' => $signature]
        );
        $response1->assertStatus(200);
        $response1->assertJson(['status' => 'success', 'result' => 'processed']);

        // Send duplicate request (same event ID)
        $response2 = $this->postJson(
            route('razorpay.webhook'),
            $payloadData,
            ['X-Razorpay-Signature' => $signature]
        );
        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'duplicate']);
    }

    public function test_webhook_idempotency_prevents_duplicate_processing_for_same_payment_id()
    {
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Bob',
            'mname' => 'C',
            'lname' => 'Doe',
            'email' => 'bob.doe@example.com',
            'fees_status' => 'Pending',
        ]);

        $paymentId = 'pay_shared_payment_123';
        $orderId = 'order_shared_order_123';

        // Payload 1 with event_id_1
        $payload1 = [
            'id' => 'event_id_1',
            'entity' => 'event',
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => $orderId,
                        'notes' => [
                            'student_id' => $student->student_id,
                            'plan_id' => 1,
                        ]
                    ]
                ]
            ]
        ];

        // Payload 2 with event_id_2 but same payment ID
        $payload2 = $payload1;
        $payload2['id'] = 'event_id_2';

        // Send first webhook
        $rawBody1 = json_encode($payload1);
        $signature1 = $this->generateSignature($rawBody1);
        $response1 = $this->postJson(route('razorpay.webhook'), $payload1, ['X-Razorpay-Signature' => $signature1]);
        $response1->assertStatus(200);
        $response1->assertJson(['status' => 'success', 'result' => 'processed']);

        // Send second webhook (different event ID, same payment ID)
        $rawBody2 = json_encode($payload2);
        $signature2 = $this->generateSignature($rawBody2);
        $response2 = $this->postJson(route('razorpay.webhook'), $payload2, ['X-Razorpay-Signature' => $signature2]);
        $response2->assertStatus(200);
        $response2->assertJson(['status' => 'duplicate']);

        // Verify the second event is recorded as duplicate in the database
        $this->assertDatabaseHas('processed_razorpay_events', [
            'event_id' => 'event_id_2',
            'payment_id' => $paymentId,
            'status' => 'duplicate',
        ]);
    }

    public function test_admin_can_sync_inprogress_payment_to_paid()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student in progress
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Alice',
            'mname' => 'D',
            'lname' => 'Doe',
            'email' => 'alice@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => 'order_alice_123',
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);
        $mockOrder = $this->createMockOrder('paid', 'pay_alice_123', 'order_alice_123');
        $mock->shouldReceive('getOrderDetails')
            ->with('order_alice_123')
            ->once()
            ->andReturn([
                'success' => true,
                'order' => $mockOrder
            ]);
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_alice_123')
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_alice_123',
                    'status' => 'captured',
                    'order_id' => 'order_alice_123',
                ]
            ]);

        // 5. Send POST request as admin to sync payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-payment', $student->student_id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('pay_alice_123', $student->razorpay_payment_id);
    }

    public function test_admin_can_bulk_sync_inprogress_payments()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin2',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create students
        $student1 = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Charlie',
            'mname' => 'E',
            'lname' => 'Doe',
            'email' => 'charlie@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => 'order_charlie_123',
        ]);

        $student2 = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Daniel',
            'mname' => 'F',
            'lname' => 'Doe',
            'email' => 'daniel@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => 'order_daniel_123',
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);
        $mockOrderPaid = $this->createMockOrder('paid', 'pay_charlie_123', 'order_charlie_123');
        $mockOrderCreated = $this->createMockOrder('created', null, 'order_daniel_123');

        $mock->shouldReceive('getOrderDetails')
            ->with('order_charlie_123')
            ->once()
            ->andReturn([
                'success' => true,
                'order' => $mockOrderPaid
            ]);
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_charlie_123')
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_charlie_123',
                    'status' => 'captured',
                    'order_id' => 'order_charlie_123',
                ]
            ]);

        $mock->shouldReceive('getOrderDetails')
            ->with('order_daniel_123')
            ->once()
            ->andReturn([
                'success' => true,
                'order' => $mockOrderCreated
            ]);
        $mock->shouldReceive('getPayments')
            ->with(['count' => 100])
            ->andReturn([
                'success' => true,
                'payments' => (object)[
                    'items' => []
                ]
            ]);

        // 5. Send POST request as admin to bulk sync
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-all-inprogress'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student1->refresh();
        $student2->refresh();

        $this->assertEquals('Paid', $student1->fees_status);
        $this->assertEquals('pay_charlie_123', $student1->razorpay_payment_id);

        $this->assertEquals('In Progress', $student2->fees_status);
        $this->assertNull($student2->razorpay_payment_id);
    }

    public function test_admin_can_sync_payment_using_receipt_lookup_when_order_id_is_null()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin3',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student with null order ID and Pending status
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'George',
            'mname' => 'G',
            'lname' => 'Doe',
            'email' => 'george@example.com',
            'fees_status' => 'Pending',
            'razorpay_order_id' => null,
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);
        $mockOrder = $this->createMockOrder('paid', 'pay_george_123', 'order_resolved_via_receipt_123');

        // Expect getOrdersByReceipt to find the order
        $mock->shouldReceive('getOrdersByReceipt')
            ->with('order_rcptid_' . $student->student_id)
            ->once()
            ->andReturn([
                'success' => true,
                'orders' => (object)[
                    'items' => [$mockOrder]
                ]
            ]);

        // Expect getOrderDetails for the resolved order ID
        $mock->shouldReceive('getOrderDetails')
            ->with('order_resolved_via_receipt_123')
            ->once()
            ->andReturn([
                'success' => true,
                'order' => $mockOrder
            ]);
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_george_123')
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_george_123',
                    'status' => 'captured',
                    'order_id' => 'order_resolved_via_receipt_123',
                ]
            ]);

        // 5. Send POST request as admin to sync payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-payment', $student->student_id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('order_resolved_via_receipt_123', $student->razorpay_order_id);
        $this->assertEquals('pay_george_123', $student->razorpay_payment_id);
    }

    public function test_admin_can_sync_payment_using_payment_id_fallback_when_order_not_found()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin4',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student with a Null order ID, In Progress status, and direct payment ID
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Henry',
            'mname' => 'H',
            'lname' => 'Doe',
            'email' => 'henry@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => null,
            'razorpay_payment_id' => 'pay_henry_direct_123',
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);

        // Expect getOrdersByReceipt to return no orders
        $mock->shouldReceive('getOrdersByReceipt')
            ->with('order_rcptid_' . $student->student_id)
            ->once()
            ->andReturn([
                'success' => true,
                'orders' => (object)[
                    'items' => []
                ]
            ]);

        // Expect getPaymentDetails to find the payment ID directly
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_henry_direct_123')
            ->once()
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_henry_direct_123',
                    'status' => 'captured',
                    'order_id' => 'order_henry_resolved_123',
                ]
            ]);

        // 5. Send POST request as admin to sync payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-payment', $student->student_id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('order_henry_resolved_123', $student->razorpay_order_id);
        $this->assertEquals('pay_henry_direct_123', $student->razorpay_payment_id);
    }

    public function test_admin_can_sync_payment_using_payment_log_fallback_when_order_not_found()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin5',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student with Null order ID and In Progress status, no payment ID
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Ian',
            'mname' => 'I',
            'lname' => 'Doe',
            'email' => 'ian@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => null,
            'razorpay_payment_id' => null,
        ]);

        // Seed paymentmode, invoice, and paymentlog
        DB::table('paymentmode')->updateOrInsert(
            ['paymode_id' => 1],
            ['paymodename' => 'Razorpay']
        );

        $invoiceId = DB::table('invoice')->insertGetId([
            'invoice_date' => now()->toDateString(),
            'total_before_discount' => 1000.00,
            'total_due' => 1000.00,
            'status' => 'ISSUED',
            'student_id' => $student->student_id,
        ]);

        DB::table('paymentlog')->insert([
            'paymode_id' => 1,
            'processdatetime' => now()->toDateTimeString(),
            'amount' => 1000.00,
            'status' => '1',
            'txnid' => 'pay_ian_log_123',
            'invoice_id' => $invoiceId,
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);

        // Expect getOrdersByReceipt to return no orders
        $mock->shouldReceive('getOrdersByReceipt')
            ->with('order_rcptid_' . $student->student_id)
            ->once()
            ->andReturn([
                'success' => true,
                'orders' => (object)[
                    'items' => []
                ]
            ]);

        // Expect getPaymentDetails to find the payment ID from the log
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_ian_log_123')
            ->once()
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_ian_log_123',
                    'status' => 'captured',
                    'order_id' => 'order_ian_resolved_123',
                ]
            ]);

        // 5. Send POST request as admin to sync payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-payment', $student->student_id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('order_ian_resolved_123', $student->razorpay_order_id);
        $this->assertEquals('pay_ian_log_123', $student->razorpay_payment_id);
    }

    public function test_admin_can_bulk_sync_using_payment_id_fallback()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin6',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student in progress with direct payment ID
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Jack',
            'mname' => 'J',
            'lname' => 'Doe',
            'email' => 'jack@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => null,
            'razorpay_payment_id' => 'pay_jack_direct_123',
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);

        // Expect getOrdersByReceipt to return no orders
        $mock->shouldReceive('getOrdersByReceipt')
            ->with('order_rcptid_' . $student->student_id)
            ->once()
            ->andReturn([
                'success' => true,
                'orders' => (object)[
                    'items' => []
                ]
            ]);

        // Expect getPaymentDetails to find the payment ID directly
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_jack_direct_123')
            ->once()
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_jack_direct_123',
                    'status' => 'captured',
                    'order_id' => 'order_jack_resolved_123',
                ]
            ]);

        // 5. Send POST request as admin to bulk sync
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-all-inprogress'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('order_jack_resolved_123', $student->razorpay_order_id);
        $this->assertEquals('pay_jack_direct_123', $student->razorpay_payment_id);
    }

    public function test_admin_can_sync_payment_using_query_parameter_payment_id_fallback()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin7',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student with a Null order ID and In Progress status, no database payment ID
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Karen',
            'mname' => 'K',
            'lname' => 'Doe',
            'email' => 'karen@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => null,
            'razorpay_payment_id' => null,
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);

        // Expect getOrdersByReceipt to return no orders
        $mock->shouldReceive('getOrdersByReceipt')
            ->with('order_rcptid_' . $student->student_id)
            ->once()
            ->andReturn([
                'success' => true,
                'orders' => (object)[
                    'items' => []
                ]
            ]);

        // Expect getPaymentDetails to find the payment ID from request query param
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_karen_query_123')
            ->once()
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_karen_query_123',
                    'status' => 'captured',
                    'order_id' => 'order_karen_resolved_123',
                ]
            ]);

        // 5. Send POST request as admin to sync payment with query parameter payment_id
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-payment', [
                'id' => $student->student_id,
                'payment_id' => 'pay_karen_query_123'
            ]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('order_karen_resolved_123', $student->razorpay_order_id);
        $this->assertEquals('pay_karen_query_123', $student->razorpay_payment_id);
    }

    public function test_admin_can_sync_payment_by_scanning_recent_payments_with_matching_email()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin8',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Leo',
            'mname' => 'L',
            'lname' => 'Doe',
            'email' => 'leo.scan@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => null,
            'razorpay_payment_id' => null,
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);

        // Expect getOrdersByReceipt to return no orders
        $mock->shouldReceive('getOrdersByReceipt')
            ->with('order_rcptid_' . $student->student_id)
            ->once()
            ->andReturn([
                'success' => true,
                'orders' => (object)[
                    'items' => []
                ]
            ]);

        // Expect getPayments scan to return a list of payments containing the email
        $mockPaymentItem = [
            'id' => 'pay_leo_scanned_123',
            'email' => 'leo.scan@example.com',
            'notes' => [],
        ];
        $mock->shouldReceive('getPayments')
            ->with(['count' => 100])
            ->once()
            ->andReturn([
                'success' => true,
                'payments' => (object)[
                    'items' => [$mockPaymentItem]
                ]
            ]);

        // Expect getPaymentDetails for the resolved payment ID
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_leo_scanned_123')
            ->once()
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_leo_scanned_123',
                    'status' => 'captured',
                    'order_id' => 'order_leo_resolved_123',
                ]
            ]);

        // 5. Send POST request as admin to sync payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-payment', $student->student_id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('order_leo_resolved_123', $student->razorpay_order_id);
        $this->assertEquals('pay_leo_scanned_123', $student->razorpay_payment_id);
    }

    public function test_admin_can_sync_payment_by_scanning_recent_payments_with_matching_student_id_notes()
    {
        // 1. Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // 2. Create admin user with permission
        $adminRole = \App\Models\Role::where('rolename', 'Admin')->first();
        $permission = \App\Models\Permission::where('permkey', 10001)->first();
        $adminRole->permissions()->attach($permission->permission_id);

        $admin = \App\Models\SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Admin',
            'mname' => 'User',
            'lname' => 'Test',
            'username' => 'syncadmin9',
            'pswd' => 'password123',
            'inactive' => false,
        ]);

        // 3. Create student
        $student = Student::create([
            'studenttype_id' => 2,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => 1,
            'fname' => 'Mike',
            'mname' => 'M',
            'lname' => 'Doe',
            'email' => 'mike@example.com',
            'fees_status' => 'In Progress',
            'razorpay_order_id' => null,
            'razorpay_payment_id' => null,
        ]);

        // 4. Mock RazorpayService API calls
        $mock = $this->mock(\App\Services\RazorpayService::class);

        // Expect getOrdersByReceipt to return no orders
        $mock->shouldReceive('getOrdersByReceipt')
            ->with('order_rcptid_' . $student->student_id)
            ->once()
            ->andReturn([
                'success' => true,
                'orders' => (object)[
                    'items' => []
                ]
            ]);

        // Expect getPayments scan to return a list of payments containing the student ID in notes
        $mockPaymentItem = [
            'id' => 'pay_mike_scanned_123',
            'email' => 'different@example.com',
            'notes' => [
                'student_id' => $student->student_id
            ],
        ];
        $mock->shouldReceive('getPayments')
            ->with(['count' => 100])
            ->once()
            ->andReturn([
                'success' => true,
                'payments' => (object)[
                    'items' => [$mockPaymentItem]
                ]
            ]);

        // Expect getPaymentDetails for the resolved payment ID
        $mock->shouldReceive('getPaymentDetails')
            ->with('pay_mike_scanned_123')
            ->once()
            ->andReturn([
                'success' => true,
                'payment' => [
                    'id' => 'pay_mike_scanned_123',
                    'status' => 'captured',
                    'order_id' => 'order_mike_resolved_123',
                ]
            ]);

        // 5. Send POST request as admin to sync payment
        $response = $this->actingAs($admin)
            ->post(route('admin.registrations.sync-payment', $student->student_id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('Paid', $student->fees_status);
        $this->assertEquals('order_mike_resolved_123', $student->razorpay_order_id);
        $this->assertEquals('pay_mike_scanned_123', $student->razorpay_payment_id);
    }
}
