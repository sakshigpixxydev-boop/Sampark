<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class LaravelLogController extends Controller
{
    public function index(Request $request)
    {
        $logDir = storage_path('logs');
        $logFiles = File::glob($logDir . '/*.log');
        
        $files = array_map(function ($filePath) {
            return [
                'name' => basename($filePath),
                'path' => $filePath,
                'size' => $this->formatBytes(filesize($filePath)),
                'raw_size' => filesize($filePath),
                'modified' => filemtime($filePath)
            ];
        }, $logFiles);

        // Sort files by modified time desc
        usort($files, function ($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });

        $selectedFile = $request->input('file', 'laravel.log');
        
        // Security check: ensure the file exists and is in the logs directory
        $selectedFilePath = $logDir . '/' . basename($selectedFile);
        
        if (!File::exists($selectedFilePath)) {
            $selectedFile = 'laravel.log';
            $selectedFilePath = $logDir . '/laravel.log';
        }

        $logs = [];
        if (File::exists($selectedFilePath)) {
            $logs = $this->parseLogFile($selectedFilePath);
        }

        // Apply filters
        $levelFilter = $request->input('level');
        $searchFilter = $request->input('search');

        if ($levelFilter || $searchFilter) {
            $logs = array_filter($logs, function ($entry) use ($levelFilter, $searchFilter) {
                if ($levelFilter && strtolower($entry['level']) !== strtolower($levelFilter)) {
                    return false;
                }
                if ($searchFilter) {
                    $searchLower = strtolower($searchFilter);
                    $msgMatch = str_contains(strtolower($entry['message']), $searchLower);
                    $traceMatch = str_contains(strtolower($entry['stack_trace']), $searchLower);
                    if (!$msgMatch && !$traceMatch) {
                        return false;
                    }
                }
                return true;
            });
        }

        // Paginate results
        $perPage = 50;
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $logs = array_values($logs);
        $currentItems = array_slice($logs, ($currentPage - 1) * $perPage, $perPage);
        $paginatedLogs = new LengthAwarePaginator(
            $currentItems,
            count($logs),
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query()
            ]
        );

        $levels = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

        return view('laravel-logs.index', compact('files', 'selectedFile', 'paginatedLogs', 'levels'));
    }

    public function clear(Request $request)
    {
        $request->validate([
            'file' => 'required|string'
        ]);

        $fileName = basename($request->input('file'));
        $filePath = storage_path('logs/' . $fileName);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error', 'Log file not found.');
        }

        File::put($filePath, '');

        return redirect()->route('laravel-logs.index', ['file' => $fileName])
            ->with('success', 'Log file cleared successfully.');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'file' => 'required|string'
        ]);

        $fileName = basename($request->input('file'));
        $filePath = storage_path('logs/' . $fileName);

        if (!File::exists($filePath)) {
            return redirect()->back()->with('error', 'Log file not found.');
        }

        // If it's laravel.log, truncate it instead of deleting to avoid issues
        if ($fileName === 'laravel.log') {
            File::put($filePath, '');
            return redirect()->route('laravel-logs.index')
                ->with('success', 'Primary log file (laravel.log) truncated successfully.');
        }

        File::delete($filePath);

        return redirect()->route('laravel-logs.index')
            ->with('success', 'Log file deleted successfully.');
    }

    private function parseLogFile($path, $maxLines = 1000)
    {
        if (!file_exists($path) || filesize($path) === 0) {
            return [];
        }

        $fileSize = filesize($path);
        $maxBytes = 5 * 1024 * 1024; // 5MB limit
        $handle = fopen($path, 'r');
        
        if ($fileSize > $maxBytes) {
            fseek($handle, -$maxBytes, SEEK_END);
            fgets($handle); // discard first partial line
        }

        $pattern = '/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*)/';
        $entries = [];
        $currentEntry = null;

        while (($line = fgets($handle)) !== false) {
            if (preg_match($pattern, $line, $matches)) {
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }
                $currentEntry = [
                    'timestamp' => $matches[1] ?? '',
                    'environment' => $matches[2] ?? '',
                    'level' => strtoupper($matches[3] ?? 'INFO'),
                    'message' => $matches[4] ?? '',
                    'stack_trace' => ''
                ];
            } else {
                if ($currentEntry) {
                    $currentEntry['stack_trace'] .= $line;
                }
            }
        }
        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        fclose($handle);

        // Reverse to get latest first
        $entries = array_reverse($entries);

        return $entries;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
