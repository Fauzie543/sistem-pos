<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function index()
    {
        // === LOG SIZE (sudah ada)
        $logPath = storage_path('logs/laravel.log');
        $logSize = File::exists($logPath) ? round(File::size($logPath) / 1024 / 1024, 2) : 0;

        // === DATABASE CHECK
        $dbStatus = 'Offline';
        $dbLatencyMs = null;

        try {
            $start = microtime(true);
            DB::select('SELECT 1'); // query ringan
            $dbLatencyMs = round((microtime(true) - $start) * 1000, 2); // ms
            $dbStatus = 'Online';
        } catch (\Throwable $e) {
            $dbStatus = 'Offline';
        }

        // === QUEUE STATUS
        // pending jobs (tabel jobs) & failed jobs (tabel failed_jobs)
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs  = DB::table('failed_jobs')->count();

        // Worker "running" itu tricky tanpa supervisor. 
        // Pendekatan simpel: kalau pendingJobs numpuk > 0 DAN tidak bergerak lama,
        // seharusnya worker mati. Versi awal: kita cuma tampilkan pending & failed.
        $queueStatus = $failedJobs > 0
            ? 'Error'
            : ($pendingJobs > 0 ? 'Processing' : 'Idle');

        $companies = Company::all(['id','name']);

        $tenantsUsage = $companies->map(function($c){
            $folder = 'company_'.$c->id; // root folder tenant
            return [
                'id' => $c->id,
                'name' => $c->name,
                'usage_mb' => $this->getFolderSizeInMB($folder),
            ];
        });

        return view('superadmin.system.index', [
            'logSize'      => $logSize,
            'dbStatus'     => $dbStatus,
            'dbLatencyMs'  => $dbLatencyMs,
            'pendingJobs'  => $pendingJobs,
            'failedJobs'   => $failedJobs,
            'queueStatus'  => $queueStatus,
            'tenantsUsage' => $tenantsUsage,
        ]);
    }

    public function clearCache(Request $request)
    {
        // eksekusi beberapa cache command
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        return back()->with('success', 'Cache, route, config, dan view berhasil dibersihkan.');
    }

    public function clearLog(Request $request)
    {
        $logPath = storage_path('logs/laravel.log');

        if (File::exists($logPath)) {
            // --- ROTATE STYLE SEDERHANA ---
            $backupName = 'laravel-' . now()->format('Y-m-d_H-i-s') . '.log';
            File::move($logPath, storage_path('logs/'.$backupName));
        }

        // buat file kosong baru supaya Laravel tidak error saat nulis log berikutnya
        File::put($logPath, '');

        return back()->with('success', 'Log berhasil di-rotate & dibersihkan.');
    }

    private function getFolderSizeInMB($folder)
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($folder)) {
            return 0;
        }

        $size = 0;
        foreach ($disk->allFiles($folder) as $file) {
            $size += $disk->size($file); // bytes
        }

        return round($size / 1024 / 1024, 2); // MB
    }

    public function status()
{
    // === DB STATUS ===
    try {
        $start = microtime(true);
        \DB::select('SELECT 1');
        $latency = round((microtime(true) - $start) * 1000, 2);
        $dbStatus = 'Online';
    } catch (\Throwable $e) {
        $dbStatus = 'Offline';
        $latency = null;
    }

    // === QUEUE STATUS ===
    $pending = \DB::table('jobs')->count();
    $failed  = \DB::table('failed_jobs')->count();
    $oldestJob = \DB::table('jobs')->orderBy('available_at', 'asc')->first();
    $queueDelay = $oldestJob
        ? now()->diffInSeconds(\Carbon\Carbon::createFromTimestamp($oldestJob->available_at))
        : 0;
    $queueStatus = $failed > 0 ? 'Error' : ($pending > 0 ? 'Processing' : 'Idle');

    // === CPU & MEMORY (cross-platform safe) ===
    if (function_exists('sys_getloadavg')) {
        $cpuLoad = sys_getloadavg()[0];
    } else {
        // fallback for Windows (Laragon)
        $cpuLoad = 0;
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $output = shell_exec('wmic cpu get loadpercentage 2>nul');
                if ($output) {
                    preg_match_all('/\d+/', $output, $matches);
                    $cpuLoad = $matches[0][0] ?? 0;
                }
            }
        } catch (\Throwable $e) {
            $cpuLoad = 0;
        }
    }

    $memUsage = round(memory_get_usage(true) / 1048576, 2);
    $memPeak = round(memory_get_peak_usage(true) / 1048576, 2);
    $phpMemLimit = ini_get('memory_limit');

    // === UPTIME ===
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        $uptime = trim(shell_exec('uptime -p')) ?: 'Unknown';
    } else {
        // Windows fallback (gunakan waktu boot)
        $uptime = shell_exec('net stats srv 2>nul');
        if (preg_match('/since (.*)/i', $uptime ?? '', $matches)) {
            $uptime = 'Running since ' . trim($matches[1]);
        } else {
            $uptime = 'N/A (Local Windows)';
        }
    }

    return response()->json([
        'dbStatus' => $dbStatus,
        'dbLatency' => $latency,
        'queueStatus' => $queueStatus,
        'pendingJobs' => $pending,
        'failedJobs' => $failed,
        'queueDelay' => $queueDelay,
        'cpuLoad' => floatval($cpuLoad),
        'memoryUsage' => $memUsage,
        'memoryPeak' => $memPeak,
        'phpMemLimit' => $phpMemLimit,
        'uptime' => trim($uptime),
        'lastUpdated' => now()->format('H:i:s'),
    ]);
}



}