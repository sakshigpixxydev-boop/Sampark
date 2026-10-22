<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\UploadBatch;
use App\Models\ProcessedRazorpayEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $signature = $request->header('X-Razorpay-Signature');
        $secret = config('razorpay.webhook_secret');

        if (empty($signature) || empty($secret)) {
            Log::error('Razorpay Webhook: Missing signature header or webhook secret configuration');
            return response()->json(['error' => 'Unauthorized'], 400);
        }

        // Verify webhook signature directly inside the function using hash_hmac
        $rawBody = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $rawBody, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::error('Razorpay Webhook: Signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($rawBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Razorpay Webhook: Invalid JSON payload');
            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        $eventId = $event['id'] ?? null;
        $eventType = $event['event'] ?? null;

        Log::info("Razorpay Webhook Received: Event ID={$eventId}, Type={$eventType}");

        if ($eventType !== 'payment.captured') {
            Log::info("Razorpay Webhook: Event type '{$eventType}' ignored");
            return response()->json(['status' => 'ignored']);
        }

        $payment = $event['payload']['payment']['entity'] ?? null;
        if (!$payment) {
            Log::error('Razorpay Webhook: Missing payment entity in payload');
            return response()->json(['error' => 'Missing payment data'], 400);
        }

        $paymentId = $payment['id'] ?? null;
        $orderId = $payment['order_id'] ?? null;
        $notes = $payment['notes'] ?? [];

        // Check if event has already been processed (idempotency check)
        $existingEvent = ProcessedRazorpayEvent::find($eventId);
        if ($existingEvent) {
            Log::info("Razorpay Webhook: Event {$eventId} already processed with status '{$existingEvent->status}'");
            return response()->json(['status' => 'duplicate']);
        }

        // Check if payment ID has already been logged under any event ID
        if ($paymentId) {
            $existingPayment = ProcessedRazorpayEvent::where('payment_id', $paymentId)->first();
            if ($existingPayment) {
                Log::info("Razorpay Webhook: Payment ID {$paymentId} already processed (found in event {$existingPayment->event_id})");
                // Record this event as duplicate to keep track
                ProcessedRazorpayEvent::create([
                    'event_id' => $eventId,
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                    'event_type' => $eventType,
                    'status' => 'duplicate',
                    'payload' => $event,
                ]);
                return response()->json(['status' => 'duplicate']);
            }
        }

        // Determine if it is a Student Single Payment or a Bulk Upload Payment
        if (isset($notes['student_id'])) {
            return $this->processStudentPayment($eventId, $paymentId, $orderId, $notes['student_id'], $notes['plan_id'] ?? null, $event);
        } elseif ($orderId && (str_starts_with($orderId, 'order_') || isset($notes['semester_batch_id']))) {
            // Check if this matches a bulk upload batch order
            return $this->processBulkUploadPayment($eventId, $paymentId, $orderId, $event);
        }

        Log::warning("Razorpay Webhook: Could not determine context for order_id={$orderId}. No processing done.");
        return response()->json(['status' => 'unrecognized_context']);
    }

    protected function processStudentPayment($eventId, $paymentId, $orderId, $studentId, $planId, $event)
    {
        try {
            $result = DB::transaction(function () use ($eventId, $paymentId, $orderId, $studentId, $planId, $event) {
                // Fetch student with pessimistic locking to prevent race conditions
                $student = Student::where('student_id', $studentId)->lockForUpdate()->first();

                if (!$student) {
                    Log::error("Razorpay Webhook: Student {$studentId} not found in database");
                    throw new \Exception("Student not found");
                }

                if ($student->fees_status === 'Paid') {
                    Log::info("Razorpay Webhook: Student {$studentId} was already marked as Paid. Skipping success workflow.");
                    ProcessedRazorpayEvent::create([
                        'event_id' => $eventId,
                        'payment_id' => $paymentId,
                        'order_id' => $orderId,
                        'event_type' => 'payment.captured',
                        'status' => 'duplicate',
                        'payload' => $event,
                    ]);
                    return 'already_paid';
                }

                // 1. Process payment success logic
                $student->fees_status = 'Paid';

                if (empty($student->subscription) && $planId) {
                    $plan = \App\Models\FeePlan::find($planId);
                    if ($plan) {
                        $student->subscription = $plan->fee_name;
                    }
                }

                if (empty($student->enrolment_no)) {
                    $student->enrolment_no = Student::generateNextEnrolmentNumber($student->student_id);
                }

                $student->save();

                // 2. Mark event as processed in idempotency table
                ProcessedRazorpayEvent::create([
                    'event_id' => $eventId,
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                    'event_type' => 'payment.captured',
                    'status' => 'processed',
                    'payload' => $event,
                ]);

                return 'processed';
            });

            // If we successfully processed the payment (not duplicate), send email notification
            if ($result === 'processed') {
                $student = Student::find($studentId);
                if ($student) {
                    $this->sendPaymentSuccessEmail($student);
                }
            }

            return response()->json(['status' => 'success', 'result' => $result]);

        } catch (\Exception $e) {
            Log::error("Razorpay Webhook: Error processing student payment: " . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    protected function processBulkUploadPayment($eventId, $paymentId, $orderId, $event)
    {
        try {
            $batch = UploadBatch::where('razorpay_order_id', $orderId)->first();

            if (!$batch) {
                // The order doesn't correspond to any created batch (perhaps the frontend callback crashed before creation)
                Log::warning("Razorpay Webhook: Bulk order {$orderId} has no corresponding UploadBatch record in DB yet.");
                return response()->json(['status' => 'waiting_for_batch_creation']);
            }

            $result = DB::transaction(function () use ($eventId, $paymentId, $orderId, $batch, $event) {
                // Lock the batch record
                $lockedBatch = UploadBatch::where('batch_id', $batch->batch_id)->lockForUpdate()->first();

                if ($lockedBatch->payment_verified || $lockedBatch->status === 'approved') {
                    Log::info("Razorpay Webhook: Bulk batch {$lockedBatch->batch_code} was already approved. Skipping success workflow.");
                    ProcessedRazorpayEvent::create([
                        'event_id' => $eventId,
                        'payment_id' => $paymentId,
                        'order_id' => $orderId,
                        'event_type' => 'payment.captured',
                        'status' => 'duplicate',
                        'payload' => $event,
                    ]);
                    return 'already_paid';
                }

                // Approve online payment (enrolling students and sending welcome emails)
                $lockedBatch->approveOnlinePayment($orderId, $paymentId, 'webhook_' . $eventId);

                ProcessedRazorpayEvent::create([
                    'event_id' => $eventId,
                    'payment_id' => $paymentId,
                    'order_id' => $orderId,
                    'event_type' => 'payment.captured',
                    'status' => 'processed',
                    'payload' => $event,
                ]);

                return 'processed';
            });

            return response()->json(['status' => 'success', 'result' => $result]);

        } catch (\Exception $e) {
            Log::error("Razorpay Webhook: Error processing bulk payment: " . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    private function sendPaymentSuccessEmail($student)
    {
        try {
            $emailService = new \App\Services\ExternalEmailService();
            $guardian = $student->guardians()->first();
            $guardianName = $guardian ? ($guardian->fname . ' ' . $guardian->lname) : '';
            $studentName = $student->fname . ' ' . $student->lname;

            $rendered = \App\Helpers\MailTemplateHelper::render('payment_success', [
                'guardian_name' => $guardianName,
                'student_name' => $studentName,
                'subscription' => $student->subscription,
            ], $student->selected_language);

            $to = [$student->email => $student->fname . ' ' . $student->lname];
            $emailService->sendSmtpMail($to, $rendered['subject'], $rendered['message']);
            Log::info("Razorpay Webhook: Success email sent to {$student->email}");
        } catch (\Exception $e) {
            Log::error('Razorpay Webhook: Payment Success Email Error: ' . $e->getMessage());
        }
    }
}
