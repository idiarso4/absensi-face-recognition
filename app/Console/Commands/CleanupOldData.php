<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Attendance;
use App\Models\Leave;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-old-data {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old attendance and leave records based on retention policies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No data will be deleted');
            $this->newLine();
        }

        $this->info('🧹 Starting data cleanup process...');
        $this->newLine();

        // Cleanup old attendance records (older than 2 years)
        $this->cleanupAttendanceRecords($dryRun);

        // Cleanup old leave records (older than 3 years)
        $this->cleanupLeaveRecords($dryRun);

        // Cleanup expired sessions
        $this->cleanupExpiredSessions($dryRun);

        // Cleanup old log files (older than 6 months)
        $this->cleanupOldLogs($dryRun);

        $this->newLine();
        $this->info('✅ Data cleanup process completed!');

        if ($dryRun) {
            $this->warn('💡 This was a dry run. Use without --dry-run flag to actually delete data.');
        }
    }

    /**
     * Clean up old attendance records
     */
    private function cleanupAttendanceRecords(bool $dryRun = false): void
    {
        $retentionDays = 365 * 2; // 2 years
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $this->info("📅 Cleaning up attendance records older than {$retentionDays} days ({$cutoffDate->format('Y-m-d')})");

        $count = Attendance::where('created_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('   ℹ️  No old attendance records found');
            return;
        }

        $this->warn("   ⚠️  Found {$count} old attendance records to delete");

        if (!$dryRun) {
            $deleted = Attendance::where('created_at', '<', $cutoffDate)->delete();
            $this->info("   ✅ Deleted {$deleted} attendance records");
        } else {
            $this->info("   🔍 Would delete {$count} attendance records");
        }

        $this->newLine();
    }

    /**
     * Clean up old leave records
     */
    private function cleanupLeaveRecords(bool $dryRun = false): void
    {
        $retentionDays = 365 * 3; // 3 years
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $this->info("📅 Cleaning up leave records older than {$retentionDays} days ({$cutoffDate->format('Y-m-d')})");

        $count = Leave::where('created_at', '<', $cutoffDate)->count();

        if ($count === 0) {
            $this->info('   ℹ️  No old leave records found');
            return;
        }

        $this->warn("   ⚠️  Found {$count} old leave records to delete");

        if (!$dryRun) {
            $deleted = Leave::where('created_at', '<', $cutoffDate)->delete();
            $this->info("   ✅ Deleted {$deleted} leave records");
        } else {
            $this->info("   🔍 Would delete {$count} leave records");
        }

        $this->newLine();
    }

    /**
     * Clean up expired sessions
     */
    private function cleanupExpiredSessions(bool $dryRun = false): void
    {
        $this->info('🗂️  Cleaning up expired user sessions');

        // Get current timestamp
        $now = Carbon::now()->timestamp;

        // Count expired sessions
        $count = DB::table('sessions')
            ->where('last_activity', '<', $now - (config('session.lifetime') * 60))
            ->count();

        if ($count === 0) {
            $this->info('   ℹ️  No expired sessions found');
            return;
        }

        $this->warn("   ⚠️  Found {$count} expired sessions to delete");

        if (!$dryRun) {
            $deleted = DB::table('sessions')
                ->where('last_activity', '<', $now - (config('session.lifetime') * 60))
                ->delete();
            $this->info("   ✅ Deleted {$deleted} expired sessions");
        } else {
            $this->info("   🔍 Would delete {$count} expired sessions");
        }

        $this->newLine();
    }

    /**
     * Clean up old log files
     */
    private function cleanupOldLogs(bool $dryRun = false): void
    {
        $retentionDays = 180; // 6 months
        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $this->info("📝 Cleaning up log files older than {$retentionDays} days ({$cutoffDate->format('Y-m-d')})");

        $logPath = storage_path('logs');
        $deletedFiles = 0;
        $totalSize = 0;

        if (!is_dir($logPath)) {
            $this->info('   ℹ️  Logs directory not found');
            return;
        }

        $files = glob($logPath . '/*.log');

        foreach ($files as $file) {
            $fileModified = Carbon::createFromTimestamp(filemtime($file));

            if ($fileModified->lt($cutoffDate)) {
                $fileSize = filesize($file);
                $totalSize += $fileSize;

                if (!$dryRun) {
                    if (unlink($file)) {
                        $deletedFiles++;
                    }
                } else {
                    $deletedFiles++;
                }
            }
        }

        if ($deletedFiles === 0) {
            $this->info('   ℹ️  No old log files found');
        } else {
            $sizeFormatted = $this->formatBytes($totalSize);
            if (!$dryRun) {
                $this->info("   ✅ Deleted {$deletedFiles} old log files ({$sizeFormatted})");
            } else {
                $this->info("   🔍 Would delete {$deletedFiles} old log files ({$sizeFormatted})");
            }
        }

        $this->newLine();
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
