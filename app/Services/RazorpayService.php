<?php

namespace App\Services;

use Exception;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected $api;
    protected $keyId;
    protected $keySecret;

    public function __construct()
    {
        $this->keyId = config('razorpay.key_id') ?: config('services.razorpay.key');
        $this->keySecret = config('razorpay.key_secret') ?: config('services.razorpay.secret');
        
        if (empty($this->keyId) || empty($this->keySecret)) {
            Log::warning('RazorpayService: Key ID or Secret is missing in config.');
        }
        
        try {
            $this->api = new Api($this->keyId, $this->keySecret);
        } catch (Exception $e) {
            Log::error('RazorpayService Constructor Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create a Razorpay order
     */
    public function createOrder($amount, $currency = 'INR', $receipt = null, $notes = [])
    {
        try {
            $orderData = [
                'receipt' => $receipt ?? 'order_' . time(),
                'amount' => $amount * 100, // Amount in paise
                'currency' => $currency,
                'notes' => $notes
            ];

            $order = $this->api->order->create($orderData);

            Log::info('Razorpay Order Created', [
                'order_id' => $order->id,
                'amount' => $order->amount,
                'receipt' => $order->receipt
            ]);

            return [
                'success' => true,
                'order_id' => $order->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'receipt' => $order->receipt,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay Order Creation Failed', [
                'error' => $e->getMessage(),
                'data' => $orderData
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Razorpay payment signature
     */
    public function verifyPaymentSignature($orderId, $paymentId, $signature)
    {
        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature
            ];

            $this->api->utility->verifyPaymentSignature($attributes);

            Log::info('Razorpay Payment Verified', [
                'order_id' => $orderId,
                'payment_id' => $paymentId
            ]);

            return [
                'success' => true,
                'message' => 'Payment signature verified successfully'
            ];
        } catch (Exception $e) {
            Log::error('Razorpay Payment Verification Failed', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
                'payment_id' => $paymentId
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch payment details
     */
    public function getPaymentDetails($paymentId)
    {
        try {
            $payment = $this->api->payment->fetch($paymentId);

            return [
                'success' => true,
                'payment' => $payment,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch order details
     */
    public function getOrderDetails($orderId)
    {
        try {
            $order = $this->api->order->fetch($orderId);

            return [
                'success' => true,
                'order' => $order,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch payments associated with a specific order ID
     */
    public function getPaymentsForOrder($orderId)
    {
        try {
            $payments = $this->api->order->fetch($orderId)->payments();

            return [
                'success' => true,
                'payments' => $payments,
            ];
        } catch (Exception $e) {
            Log::error('Razorpay Fetch Payments for Order Failed', [
                'error' => $e->getMessage(),
                'order_id' => $orderId
            ]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch orders by receipt ID
     */
    public function getOrdersByReceipt($receipt)
    {
        try {
            $orders = $this->api->order->all(['receipt' => $receipt]);

            return [
                'success' => true,
                'orders' => $orders,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch payments list
     */
    public function getPayments($options = [])
    {
        try {
            $payments = $this->api->payment->all($options);

            return [
                'success' => true,
                'payments' => $payments,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Razorpay key ID for frontend
     */
    public function getKeyId()
    {
        return $this->keyId;
    }
}
