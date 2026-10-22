<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentAnnouncement;
use App\Models\StudentAnnouncementQueue;
use App\Services\ExternalEmailService;
use Illuminate\Support\Facades\Log;

class ProcessStudentAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:process-announcements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the student announcement queue.';

    protected $emailService;

    public function __construct(ExternalEmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process student announcement queue...');

        $announcements = StudentAnnouncement::whereIn('status', ['READY', 'PENDING'])->get();

        if ($announcements->isEmpty()) {
            $this->info('No announcements to process.');
            return;
        }

        foreach ($announcements as $announcement) {
            $this->info("Processing Announcement ID: {$announcement->id}");

            if ($announcement->status === 'READY') {
                $announcement->update(['status' => 'PENDING']);
            }

            $queueEntries = StudentAnnouncementQueue::where('announcement_id', $announcement->id)
                ->where('status', 'PENDING')
                ->with('student')
                ->limit(20)
                ->get();

            if ($queueEntries->isEmpty()) {
                $announcement->update(['status' => 'DONE']);
                $this->info("Announcement ID {$announcement->id} completed.");
                continue;
            }

            foreach ($queueEntries as $entry) {
                /** @var \App\Models\StudentAnnouncementQueue $entry */
                $student = $entry->student;
                $success = false;

                if ($announcement->communication_type === 'Email') {
                    if ($student && $student->email) {
                        $this->info("Sending Email to student: {$student->fname} ({$student->email})");

                        $attachment = null;
                        if ($announcement->attachment) {
                            $attachment = storage_path('app/public/' . $announcement->attachment);
                        }

                        $success = $this->emailService->sendSmtpMail(
                            [$student->email => "{$student->fname} {$student->lname}"],
                            $announcement->subject ?? 'Announcement',
                            $announcement->message,
                            null, // CC
                            $attachment
                        );
                    } else {
                        $entry->update(['status' => 'FAILED', 'error_message' => 'No email address found.']);
                        continue;
                    }
                } elseif ($announcement->communication_type === 'WhatsApp') {
                    // Placeholder for WhatsApp integration
                    if ($student && $student->whatsapp_no) {
                        $this->info("Sending WhatsApp to student: {$student->fname} ({$student->whatsapp_no})");
                        // Logic to integrate with WhatsApp API would go here
                        // For now, we simulate success if the number exists
                        Log::info("WhatsApp Integration Placeholder: Sent to {$student->whatsapp_no}");
                        $success = true; 
                    } else {
                        $entry->update(['status' => 'FAILED', 'error_message' => 'No WhatsApp number found.']);
                        continue;
                    }
                }

                if ($success) {
                    $entry->update(['status' => 'SENT', 'sent_at' => now()]);
                    $this->info('Sent successfully.');
                } else {
                    $entry->update(['status' => 'FAILED', 'error_message' => 'Service error during sending.']);
                }
            }
        }

        $this->info('Processing completed.');
    }
}
