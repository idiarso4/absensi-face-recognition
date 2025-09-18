<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportGenerated;
use Throwable;

class SmokeTestReports extends Command
{
    protected $signature = 'app:smoketest-reports {--mail : Also send a test mailable using the log mailer}';

    protected $description = 'Quickly smoke-test report generation and optional mailable without hitting HTTP routes';

    public function handle(): int
    {
        $svc = app(ReportService::class);

        $monthStr = now()->format('Y-m');
        $this->info('Testing attendance report for month ' . $monthStr);
        $this->line($this->testAttendance($svc, $monthStr));

        $this->info('Testing leave report for month ' . $monthStr);
        $this->line($this->testLeave($svc, $monthStr));

        $this->info('Testing monthly summary report');
        $this->line($this->testMonthlySummary($svc));

        if ($this->option('mail')) {
            $this->info('Sending test mailable via log mailer');
            $this->line($this->testMail());
        }

        return 0;
    }

    private function describeResult($res): string
    {
        try {
            if (is_string($res)) {
                return 'OK string length=' . strlen($res);
            }
            if ($res instanceof \Symfony\Component\HttpFoundation\Response) {
                return 'OK response=' . get_class($res) . ' status=' . $res->getStatusCode();
            }
            return 'OK type=' . (is_object($res) ? get_class($res) : gettype($res));
        } catch (Throwable $e) {
            return 'ERROR describing result: ' . $e->getMessage();
        }
    }

    private function testAttendance(ReportService $svc, string $monthStr): string
    {
        try {
            $res = $svc->generateAttendanceReport(['month' => $monthStr]);
            return $this->describeResult($res);
        } catch (Throwable $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    private function testLeave(ReportService $svc, string $monthStr): string
    {
        try {
            $res = $svc->generateLeaveReport(['month' => $monthStr]);
            return $this->describeResult($res);
        } catch (Throwable $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    private function testMonthlySummary(ReportService $svc): string
    {
        try {
            $res = $svc->generateMonthlySummaryReport((int) now()->year, (int) now()->month);
            return $this->describeResult($res);
        } catch (Throwable $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }

    private function testMail(): string
    {
        try {
            Mail::mailer('log')
                ->to('devnull@example.com')
                ->send(new ReportGenerated('attendance', 'PDF TEST CONTENT', 'test.pdf'));
            return 'OK mail logged';
        } catch (Throwable $e) {
            return 'ERROR: ' . $e->getMessage();
        }
    }
}

