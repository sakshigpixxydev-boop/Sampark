<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckAgreementExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agreements:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired agreements and notify relevant parties';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired agreements...');

        // 1. Check School Leads (expired today or already expired but no notification sent)
        $expiredLeads = \App\Models\SchoolLead::where('tc_expiry_date', '<=', now())
            ->whereNull('expiry_notification_sent_at')
            ->get();

        $emailService = new \App\Services\ExternalEmailService();

        foreach ($expiredLeads as $lead) {
            $this->info("Processing lead: {$lead->school_name}");

            $variables = [
                'school_name' => $lead->school_name,
                'tc_content' => $lead->tc_content,
            ];
            $emailData = \App\Helpers\MailTemplateHelper::render('agreement_expiry', $variables);

            $subject = $emailData ? $emailData['subject'] : "Agreement Expired: {$lead->school_name}";
            $messageHtml = $emailData ? $emailData['message'] : '';

            if ($lead->email) {
                // Notifying the school if email exists
                $emailService->sendSmtpMail(
                    [$lead->email => $lead->school_name],
                    $subject,
                    $messageHtml
                );
            }

            // Mark as sent
            $lead->update(['expiry_notification_sent_at' => now()]);
        }

        // 2. Check SysUsers (Initiators) - simpler check
        $expiredUsers = \App\Models\SysUser::where('tc_expiry_date', '<=', now())
            ->whereNull('expiry_notification_sent_at')
            ->get();

        foreach ($expiredUsers as $user) {
            $this->info("Processing user: {$user->fname}");

            $variables = [
                'fname' => $user->fname,
                'tc_content' => $user->tc_content,
            ];
            $emailData = \App\Helpers\MailTemplateHelper::render('initiator_agreement_expiry', $variables);

            $subject = $emailData ? $emailData['subject'] : "Your Initiator Agreement Has Expired";
            $messageHtml = $emailData ? $emailData['message'] : '';

            $emailService->sendSmtpMail(
                [$user->email => $user->fname . ' ' . $user->lname],
                $subject,
                $messageHtml
            );

            $user->update(['expiry_notification_sent_at' => now()]);
        }

        $this->info('Expiry check completed.');
    }
}
