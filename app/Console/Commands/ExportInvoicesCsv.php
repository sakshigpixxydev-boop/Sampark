<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoiceCsvExportService;
use Illuminate\Console\Command;

class ExportInvoicesCsv extends Command
{
    protected $signature = 'invoices:export-csv {--out= : File to write (default: stdout)}';

    protected $description = 'Export every invoice as CSV, identical to the /invoices export, for verification';

    public function handle(InvoiceCsvExportService $exporter): int
    {
        $query = Invoice::with(InvoiceCsvExportService::eagerLoads());
        $invoices = $exporter->applyOrdering($query)->get();

        $out = $this->option('out');
        $handle = $out ? fopen($out, 'w') : fopen('php://stdout', 'w');

        $exporter->writeTo($handle, $invoices);
        fclose($handle);

        if ($out) {
            $this->info("Wrote {$invoices->count()} invoice(s) to {$out}");
        }

        return self::SUCCESS;
    }
}
