<?php

namespace App\Console\Commands;

use App\Services\RazorpayPaymentCsvImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportRazorpayPaymentsCsv extends Command
{
    protected $signature = 'razorpay:import-payments
        {file : Path to the Razorpay payments export (.csv, .xlsx or .xls)}
        {--dry-run : Analyse and write reports only; performs no writes}
        {--statuses=captured_authorized : captured | captured_authorized | all}
        {--amount-unit=auto : auto | rupees | paise}
        {--no-create-invoices : Do not create invoices for payments we have no invoice for}
        {--no-correct-amounts : Leave existing amounts and payment dates alone}
        {--no-mark-paid : Do not set student.fees_status to Paid}
        {--limit= : Process only the first N rows}
        {--force : Required to write when the app environment is production}';

    protected $description = 'Reconcile a Razorpay payments export against our invoices, payment logs and students';

    public function handle(RazorpayPaymentCsvImporter $importer): int
    {
        $path = $this->argument('file');
        if (!str_starts_with($path, '/')) {
            $path = base_path($path);
        }

        $dryRun = (bool) $this->option('dry-run');

        if (!$dryRun && app()->environment('production') && !$this->option('force')) {
            $this->error('Refusing to write in production without --force.');
            return self::FAILURE;
        }

        $this->info($dryRun ? 'DRY RUN - no writes will be performed.' : 'APPLYING changes.');
        $this->line("Reading {$path}");

        try {
            $counts = $importer->run($path, [
                'dry_run' => $dryRun,
                'statuses' => $this->option('statuses'),
                'amount_unit' => $this->option('amount-unit'),
                'create_missing_invoices' => !$this->option('no-create-invoices'),
                'correct_amounts' => !$this->option('no-correct-amounts'),
                'mark_paid' => !$this->option('no-mark-paid'),
                'limit' => $this->option('limit'),
            ]);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            Log::error('razorpay:import-payments failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $runId = date('Ymd_His');
        $dir = $importer->writeReports($runId);

        $this->newLine();
        $this->table(['Metric', 'Value'], collect($counts)->map(
            fn ($v, $k) => [$k, is_bool($v) ? ($v ? 'yes' : 'no') : $v]
        )->values()->all());

        $this->newLine();
        if ($counts['amount_unit'] === 'paise') {
            $this->warn('Amounts were read as PAISE and divided by 100. Check a row in plan.csv before applying.');
        } else {
            $this->line('Amounts were read as RUPEES and used as-is. Check a row in plan.csv before applying.');
        }

        $this->line("Reports: {$dir}");

        if ($counts['blocked'] > 0) {
            $this->warn("{$counts['blocked']} row(s) blocked - see blocked.csv");
            foreach (array_slice($importer->getBlocked(), 0, 10) as $b) {
                $this->line("  {$b['payment_id']}  {$b['reason']}  {$b['detail']}");
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry run complete. Re-run without --dry-run to apply.');
        } else {
            $this->newLine();
            $this->info('Applied. Re-run to confirm it reports zero planned changes.');
        }

        return self::SUCCESS;
    }
}
