<?php

namespace App\Notifications;

use App\Models\SemesterBatch;
use App\Services\ExternalEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SemesterEnrollmentReminderNotification extends Notification
{
    use Queueable;

    public $currentBatch;
    public $nextBatch;

    /**
     * Create a new notification instance.
     */
    public function __construct(SemesterBatch $currentBatch, SemesterBatch $nextBatch)
    {
        $this->currentBatch = $currentBatch;
        $this->nextBatch = $nextBatch;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Send the notification using ExternalEmailService.
     */
    public function toMail($notifiable)
    {
        $messageHtml = $this->buildEmailContent($notifiable);
        
        $emailService = new ExternalEmailService();
        $emailService->sendSmtpMail(
            [$notifiable->email => $notifiable->firstname],
            'Next Semester Enrollment Reminder - ' . ($this->nextBatch->display_name ?? 'N/A'),
            $messageHtml
        );
    }

    /**
     * Build the email content.
     */
    private function buildEmailContent($notifiable): string
    {
        $content = '<p>Dear ' . htmlspecialchars($notifiable->firstname) . ',</p>';
        $content .= '<p>Your current semester <strong>' . htmlspecialchars($this->currentBatch->display_name ?? 'N/A') . '</strong> has been completed.</p>';
        $content .= '<p>We noticed that you have not yet enrolled in the next semester: <strong>' . htmlspecialchars($this->nextBatch->display_name ?? 'N/A') . '</strong></p>';
        
        $content .= '<h3>Next Semester Details:</h3>';
        $content .= '<ul>';
        $content .= '<li><strong>Semester Name:</strong> ' . htmlspecialchars($this->nextBatch->display_name ?? 'N/A') . '</li>';
        $content .= '<li><strong>Start Date:</strong> ' . ($this->nextBatch->start_date ? $this->nextBatch->start_date->format('d M Y') : 'N/A') . '</li>';
        $content .= '<li><strong>End Date:</strong> ' . ($this->nextBatch->end_date ? $this->nextBatch->end_date->format('d M Y') : 'N/A') . '</li>';
        $content .= '<li><strong>Fees:</strong> ₹' . number_format($this->nextBatch->fees, 2) . '</li>';
        $content .= '</ul>';
        
        $content .= '<p><a href="' . url('/registrations') . '" style="background-color: #00bde0; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Enroll Now</a></p>';
        $content .= '<p>Please enroll in the next semester to continue your learning journey.</p>';
        $content .= '<p>Thank you for being part of Pramukh Sankalp!</p>';
        
        return $content;
    }
}
