<?php

namespace App\Services;

/**
 * Reads a .csv/.txt/.xlsx/.xls file into a plain array of rows.
 *
 * Shared by the invoice-export importer and the Razorpay payments importer so
 * both accept the same file formats and canonicalise values the same way.
 */
class TabularFileReader
{
    public const SPREADSHEET_EXTENSIONS = ['xlsx', 'xls'];

    /**
     * @return array<int,array<int,mixed>> Rows including the header row.
     */
    public function read(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException("File not readable: {$path}");
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $records = in_array($extension, self::SPREADSHEET_EXTENSIONS, true)
            ? $this->readSpreadsheet($path)
            : $this->readDelimited($path);

        if (empty($records)) {
            throw new \RuntimeException('The file is empty.');
        }

        // Strip a UTF-8 BOM from the very first cell.
        if (isset($records[0][0])) {
            $records[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $records[0][0]);
        }

        return $records;
    }

    /**
     * Read a delimited text file, sniffing comma vs tab vs semicolon from the
     * header line. Razorpay exports are frequently saved tab-separated.
     */
    protected function readDelimited(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Could not open file: {$path}");
        }

        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = $this->sniffDelimiter((string) $firstLine);

        $records = [];
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $records[] = $data;
        }
        fclose($handle);

        return $records;
    }

    protected function sniffDelimiter(string $line): string
    {
        $counts = [
            ',' => substr_count($line, ','),
            "\t" => substr_count($line, "\t"),
            ';' => substr_count($line, ';'),
        ];
        arsort($counts);
        $best = array_key_first($counts);

        return $counts[$best] > 0 ? $best : ',';
    }

    /**
     * setReadDataOnly() skips formatting, keeping memory flat. Dates come back
     * as Excel serial numbers; normaliseDate() converts them.
     */
    protected function readSpreadsheet(string $path): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            throw new \RuntimeException('Excel support requires phpoffice/phpspreadsheet. Please upload a CSV instead.');
        }

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $records = $spreadsheet->getSheet(0)->toArray(null, true, false, false);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        while (!empty($records) && $this->isBlankRecord(end($records))) {
            array_pop($records);
        }

        return array_values($records);
    }

    public function isBlankRecord($record): bool
    {
        if (!is_array($record)) {
            return true;
        }
        foreach ($record as $cell) {
            if ($cell !== null && trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    /**
     * Trim trailing empty header cells, which Excel and some CSV writers pad.
     */
    public function trimTrailingBlanks(array $header): array
    {
        $header = array_map(fn ($h) => trim((string) $h), $header);
        while (!empty($header) && end($header) === '') {
            array_pop($header);
        }

        return $header;
    }

    /**
     * Accept Y-m-d, an Excel serial number, a unix timestamp, or the common
     * d-m-Y / d/m/Y typings. Unrecognised values are returned untouched so the
     * caller can report them.
     */
    public function normaliseDate(string $value, bool $withTime = false): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $outFormat = $withTime ? 'Y-m-d H:i:s' : 'Y-m-d';

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $withTime ? $value . ' 00:00:00' : $value;
        }

        if (is_numeric($value)) {
            $number = (float) $value;

            // Unix timestamp: Razorpay's API created_at. 10^9 is 2001.
            if ($number > 1000000000) {
                return date($outFormat, (int) $number);
            }

            // Excel serial date; 20000 is 1954, past any plausible year value.
            if ($number > 20000 && $number < 90000) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($number)->format($outFormat);
                } catch (\Throwable $e) {
                    return $value;
                }
            }
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d\TH:i:sP', 'd-m-Y H:i:s', 'd/m/Y H:i:s', 'd-m-Y', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd M Y', 'd M Y H:i:s'] as $format) {
            $parsed = \DateTime::createFromFormat($format, $value);
            if ($parsed !== false) {
                return $parsed->format($outFormat);
            }
        }

        try {
            return (new \DateTime($value))->format($outFormat);
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
