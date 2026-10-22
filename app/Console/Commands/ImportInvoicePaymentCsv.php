<?php

namespace App\Console\Commands;

use App\Services\InvoicePaymentCsvImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportInvoicePaymentCsv extends Command
{
    protected $signature = 'invoices:import-payment-csv
        {file : Path to the invoice export (.csv, .xlsx or .xls)}
        {--dry-run : Analyse and write reports only; performs no writes}
        {--create-missing : Insert invoices (with their explicit INV id) when they do not exist}
        {--sync-fees-status : Also set student.fees_status to Paid}
        {--only= : Comma-separated INV numbers to process}
        {--limit= : Process only the first N data rows}
        {--force : Required to write when the app environment is production}';

    protected $description = 'Import an invoice export (CSV or Excel) back into the DB so the export reproduces it exactly';

    public function handle(InvoicePaymentCsvImporter $importer): int
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
                'create_missing' => (bool) $this->option('create-missing'),
                'sync_fees_status' => (bool) $this->option('sync-fees-status'),
                'only' => $this->option('only'),
                'limit' => $this->option('limit'),
            ]);
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            Log::error('invoices:import-payment-csv failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $runId = date('Ymd_His');
        $dir = $importer->writeReports($runId);

        $this->newLine();
        $this->table(['Metric', 'Count'], collect($counts)->map(
            fn ($v, $k) => [$k, is_bool($v) ? ($v ? 'yes' : 'no') : $v]
        )->values()->all());

        $this->newLine();
        $this->line("Reports: {$dir}");

        if ($counts['blocked'] > 0) {
            $this->warn("{$counts['blocked']} row(s) blocked - see blocked.csv");
            foreach (array_slice($importer->getBlocked(), 0, 10) as $b) {
                $this->line("  {$b['invoice_number']}  {$b['reason']}  {$b['detail']}");
            }
            if ($counts['blocked'] > 10) {
                $this->line('  ... see the report for the rest');
            }
        }

        if ($counts['extras'] > 0) {
            $this->warn("{$counts['extras']} invoice(s) exist in the DB but not in the CSV - each adds a row to a re-export (see extras.csv)");
        }

        if ($counts['readonly_diffs'] > 0) {
            $this->warn("{$counts['readonly_diffs']} read-only cell(s) differ (names/email/address/registration date) - reported, not written (see readonly-diffs.csv)");
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry run complete. Re-run without --dry-run to apply.');
        } else {
            $this->newLine();
            $this->info('Applied. Re-run the command to confirm it reports zero planned changes.');
        }

        return ($counts['blocked'] > 0 && !$dryRun) ? self::FAILURE : self::SUCCESS;
    }
}
