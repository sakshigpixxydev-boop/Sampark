<?php

namespace App\Http\Controllers;

use App\Rules\SecureCsvFile;
use App\Services\InvoicePaymentCsvImporter;
use App\Services\RazorpayPaymentCsvImporter;
use App\Services\TabularFileReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Browser-driven runner for the invoice payment CSV import.
 *
 * This exists because the live environment is operated without shell access:
 * the same InvoicePaymentCsvImporter that backs the artisan command is driven
 * from an admin page instead. Preview always runs first; Apply re-reads the
 * exact bytes that were previewed.
 */
class InvoicePaymentImportController extends Controller
{
    private const CONFIRM_PHRASE = 'IMPORT PAYMENTS';

    public function form()
    {
        return view('invoices.import-payments', [
            'result' => null,
            'runId' => null,
            'applied' => false,
        ]);
    }

    /**
     * Dry run: analyse the uploaded CSV and render the full report.
     */
    public function preview(Request $request)
    {
        // SecureCsvFile already permits .csv/.txt/.xls/.xlsx.
        $request->validate([
            'csv_file' => ['required', 'file', new SecureCsvFile()],
        ], [], ['csv_file' => 'file']);

        $runId = date('Ymd_His');
        $dir = storage_path('app/invoice-csv-import/' . $runId);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        // Keep the original extension: the importer picks its reader from it.
        $extension = strtolower($request->file('csv_file')->getClientOriginalExtension()) ?: 'csv';
        $request->file('csv_file')->move($dir, 'input.' . $extension);
        $path = $dir . '/input.' . $extension;

        return $this->execute($path, $runId, $this->optionsFrom($request, $path, true), false);
    }

    /**
     * Apply the previously previewed run.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'run_id' => ['required', 'regex:/^\d{8}_\d{6}$/'],
            'confirm_phrase' => ['required', 'string'],
        ]);

        if (trim($request->input('confirm_phrase')) !== self::CONFIRM_PHRASE) {
            return redirect()->route('invoices.import-payments.form')
                ->with('error', 'Confirmation phrase did not match. Nothing was written.');
        }

        $runId = $request->input('run_id');
        $path = collect(glob(storage_path('app/invoice-csv-import/' . $runId . '/input.*')))->first();

        if (!$path || !is_file($path)) {
            return redirect()->route('invoices.import-payments.form')
                ->with('error', 'The previewed upload has expired. Please upload the CSV again.');
        }

        return $this->execute($path, $runId, $this->optionsFrom($request, $path, false), true);
    }

    /**
     * Download one of a run's report files.
     */
    public function report(string $runId, string $file)
    {
        if (!preg_match('/^\d{8}_\d{6}$/', $runId)) {
            abort(404);
        }

        $allowed = ['plan.csv', 'blocked.csv', 'warnings.csv', 'readonly-diffs.csv', 'extras.csv', 'summary.json'];
        if (!in_array($file, $allowed, true)) {
            abort(404);
        }

        $path = storage_path('app/invoice-csv-import/' . $runId . '/' . $file);
        abort_unless(is_file($path), 404);

        return response()->download($path, $runId . '-' . $file);
    }

    /**
     * Which importer handles this file? Decided from the header, so the
     * operator does not have to know which export they downloaded.
     */
    private function detectFormat(string $path): string
    {
        $records = app(TabularFileReader::class)->read($path);
        $header = app(TabularFileReader::class)->trimTrailingBlanks($records[0] ?? []);

        if (InvoicePaymentCsvImporter::matchesHeader($header)) {
            return 'invoice';
        }
        if (RazorpayPaymentCsvImporter::matchesHeader($header)) {
            return 'razorpay';
        }

        return 'unknown';
    }

    private function optionsFrom(Request $request, string $path, bool $dryRun): array
    {
        $format = $this->detectFormat($path);

        $options = ['dry_run' => $dryRun, 'format' => $format];

        if ($format === 'razorpay') {
            return $options + [
                'statuses' => $request->input('statuses', 'captured_authorized'),
                'amount_unit' => $request->input('amount_unit', 'auto'),
                'create_missing_invoices' => $request->boolean('create_missing', true),
                'correct_amounts' => $request->boolean('correct_amounts', true),
                'mark_paid' => $request->boolean('sync_fees_status', true),
            ];
        }

        return $options + [
            'create_missing' => $request->boolean('create_missing'),
            'sync_fees_status' => $request->boolean('sync_fees_status'),
        ];
    }

    private function execute(string $path, string $runId, array $options, bool $applied)
    {
        // The verification pass re-renders every row.
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $format = $options['format'];
        unset($options['format']);

        if ($format === 'unknown') {
            return redirect()->route('invoices.import-payments.form')
                ->with('error', 'Unrecognised file. Upload either an invoice export (23 columns) or a Razorpay payments export (with id, amount, status and order_id columns).');
        }

        $importer = $format === 'razorpay'
            ? app(RazorpayPaymentCsvImporter::class)
            : app(InvoicePaymentCsvImporter::class);

        try {
            $counts = $importer->run($path, $options);
        } catch (\Throwable $e) {
            Log::error('Payment import failed: ' . $e->getMessage());

            return redirect()->route('invoices.import-payments.form')
                ->with('error', 'Import failed, nothing was written: ' . $e->getMessage());
        }

        $importer->writeReports($runId);

        $result = [
            'format' => $format,
            'counts' => $counts,
            'plan' => array_slice($importer->getPlan(), 0, 200),
            'planTotal' => count($importer->getPlan()),
            'blocked' => $importer->getBlocked(),
            'warnings' => array_slice($importer->getWarnings(), 0, 200),
            'warningsTotal' => count($importer->getWarnings()),
        ];

        if ($format === 'invoice') {
            $result += [
                'readonlyDiffs' => array_slice($importer->getReadonlyDiffs(), 0, 200),
                'readonlyTotal' => count($importer->getReadonlyDiffs()),
                'extras' => $importer->getExtras(),
            ];
        } else {
            $result += ['readonlyDiffs' => [], 'readonlyTotal' => 0, 'extras' => []];
        }

        return view('invoices.import-payments', [
            'result' => $result,
            'runId' => $runId,
            'applied' => $applied,
            'options' => $options,
            'confirmPhrase' => self::CONFIRM_PHRASE,
        ]);
    }
}
