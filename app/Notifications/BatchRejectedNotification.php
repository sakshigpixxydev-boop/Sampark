<?php

namespace App\Notifications;

use App\Models\UploadBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BatchRejectedNotification extends Notification
{
    use Queueable;

    public $batch;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(UploadBatch $batch, $reason)
    {
        $this->batch = $batch;
        $this->reason = $reason;
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
            ->subject('Upload Batch Rejected - ' . $this->batch->batch_code)
            ->line('Your student upload batch has been rejected by the accountant.')
            ->line('**Batch Code:** ' . $this->batch->batch_code)
            ->line('**Total Students:** ' . $this->batch->total_students)
            ->line('**Rejection Reason:**')
            ->line($this->reason)
            ->line('Please review the rejection reason and make necessary corrections before resubmitting.')
            ->action('View Batch Details', url('/students/bulk-upload'))
            ->line('If you have any questions, please contact the accountant.');
    }
}
