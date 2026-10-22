<?php

namespace App\Jobs;

use App\Services\ExternalEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkShiftCompleteEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $totalStudents;
    protected $targetBatchName;
    protected $adminEmail;

    /**
     * Create a new job instance.
     */
    public function __construct($totalStudents, $targetBatchName, $adminEmail)
    {
        $this->totalStudents = $totalStudents;
        $this->targetBatchName = $targetBatchName;
        $this->adminEmail = $adminEmail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $emailService = new ExternalEmailService();
            
            $toEmail = 'chiragkanjariya0712+sankalpmigration@gmail.com';
            $subject = 'Bulk Student Batch Shift Migration Completed';
            
            $wrapperStart = '<div style="font-family: \'Outfit\', \'Inter\', sans-serif; background-color: #f7f9fa; padding: 30px 15px; margin: 0; width: 100%;"><div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 5px solid #00bde0;"><div style="background-color: #ffffff; padding: 25px; text-align: center; border-bottom: 1px solid #edf2f7;"><h1 style="color: #1a202c; font-size: 22px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">PRAMUKH SANKALP</h1><p style="color: #718096; font-size: 13px; margin: 5px 0 0 0; letter-spacing: 1px;">PRAMUKH ACADEMY</p></div><div style="padding: 30px 25px; color: #2d3748; line-height: 1.6; font-size: 15px;">';
            $wrapperEnd = '</div></div></div>';

            $message = $wrapperStart . 
                       '<h3>Bulk Batch Shift Completed</h3>' .
                       '<p>Hello Admin,</p>' .
                       '<p>We are pleased to inform you that the bulk student batch shifting process has been completed successfully.</p>' .
                       '<table style="width: 100%; border-collapse: collapse; margin-top: 15px;">' .
                       '<tr><td style="padding: 8px; border-bottom: 1px solid #edf2f7; font-weight: bold;">Target Batch:</td><td style="padding: 8px; border-bottom: 1px solid #edf2f7;">' . e($this->targetBatchName) . '</td></tr>' .
                       '<tr><td style="padding: 8px; border-bottom: 1px solid #edf2f7; font-weight: bold;">Total Students:</td><td style="padding: 8px; border-bottom: 1px solid #edf2f7;">' . $this->totalStudents . '</td></tr>' .
                       '<tr><td style="padding: 8px; border-bottom: 1px solid #edf2f7; font-weight: bold;">Completion Time:</td><td style="padding: 8px; border-bottom: 1px solid #edf2f7;">' . now()->format('Y-m-d H:i:s') . '</td></tr>' .
                       '</table>' .
                       '<p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Warm regards,<br><strong>Pramukh SANKALP System</strong></p>' . 
                       $wrapperEnd;

            $to = [$toEmail => 'Sankalp Admin'];
            $emailService->sendSmtpMail($to, $subject, $message);
            Log::info("Bulk Shift completion email sent to admin {$toEmail}");
        } catch (\Exception $e) {
            Log::error('Bulk Shift Completion Email Job Error: ' . $e->getMessage());
        }
    }
}
