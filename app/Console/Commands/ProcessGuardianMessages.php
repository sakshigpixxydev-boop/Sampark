<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GuardianMessage;
use App\Models\GuardianMessageQueue;
use App\Services\ExternalEmailService;
use Illuminate\Support\Facades\Log;

class ProcessGuardianMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guardians:process-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the guardian message queue in batches.';

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
        $this->info('Starting to process guardian message queue...');

        // Get messages that are not yet DONE
        $messages = GuardianMessage::whereIn('status', ['READY', 'PENDING'])->get();

        if ($messages->isEmpty()) {
            $this->info('No messages to process.');
            return;
        }

        foreach ($messages as $message) {
            $this->info("Processing message ID: {$message->id} - Subject: {$message->subject}");

            // Set message status to PENDING if it's currently READY
            if ($message->status === 'READY') {
                $message->update(['status' => 'PENDING']);
            }

            // Get next batch of queue entries for this message
            $queueEntries = GuardianMessageQueue::where('guardian_message_id', $message->id)
                ->where('status', 'PENDING')
                ->with('guardian')
                ->limit(10) // Small batch size for reliability
                ->get();

            if ($queueEntries->isEmpty()) {
                // If no more PENDING entries for this message, mark message as DONE
                $message->update(['status' => 'DONE']);
                $this->info("Message ID {$message->id} completed.");
                continue;
            }

            foreach ($queueEntries as $entry) {
                $guardian = $entry->guardian;
                
                if ($guardian && $guardian->email) {
                    $this->info("Sending to guardian: {$guardian->fname} ({$guardian->email})");
                    
                    $success = $this->emailService->sendSmtpMail(
                        [$guardian->email => "{$guardian->fname} {$guardian->lname}"],
                        $message->subject,
                        $message->message
                    );

                    if ($success) {
                        $entry->update(['status' => 'SENT']);
                        $this->info('Sent successfully.');
                    } else {
                        Log::error("Failed to send message ID {$message->id} to guardian ID {$guardian->guardian_id}");
                        $this->error("Failed to send email to {$guardian->email}");
                    }
                } else {
                    $this->warn("No email for guardian ID {$entry->user_id}. Marking as SENT to avoid stuck queue.");
                    $entry->update(['status' => 'SENT']);
                }
            }
        }

        $this->info('Processing completed.');
    }
}
