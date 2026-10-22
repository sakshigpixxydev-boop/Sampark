<?php

namespace App\Notifications;

use App\Models\UploadBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmationNotification extends Notification
{
    use Queueable;

    public $batch;

    /**
     * Create a new notification instance.
     */
    public function __construct(UploadBatch $batch)
    {
        $this->batch = $batch;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Confirmed - ' . $this->batch->batch_code)
            ->line('Great news! Your student upload batch payment has been verified and approved.')
            ->line('**Batch Code:** ' . $this->batch->batch_code)
            ->line('**Total Students:** ' . $this->batch->total_students)
            ->line('**Total Fees:** ₹' . number_format($this->batch->total_fees, 2))
            ->line('**Transaction ID:** ' . $this->batch->transaction_id)
            ->line('**Payment Date:** ' . $this->batch->payment_date->format('d M Y'))
            ->line('All students in this batch have been successfully registered in the system.')
            ->action('View Students', url('/students'))
            ->line('Thank you for using Pramukh Sankalp!');
    }
}
