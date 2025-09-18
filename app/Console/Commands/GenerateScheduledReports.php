<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-scheduled-reports
                            {--dry-run : Show which templates would be generated without actually generating them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate reports for templates that are scheduled to run now';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for scheduled report templates...');

        $templates = \App\Models\ReportTemplate::where('is_active', true)
            ->where('schedule_frequency', '!=', 'manual')
            ->get();

        if ($templates->isEmpty()) {
            $this->info('ℹ️  No active scheduled templates found.');
            return 0;
        }

        $this->info("📋 Found {$templates->count()} active scheduled templates");

        $toGenerate = [];
        foreach ($templates as $template) {
            if ($template->shouldGenerateNow()) {
                $toGenerate[] = $template;
            }
        }

        if (empty($toGenerate)) {
            $this->info('⏰ No templates are scheduled to run at this time.');
            return 0;
        }

        $this->info("🚀 " . count($toGenerate) . " templates are ready to generate:");

        foreach ($toGenerate as $template) {
            $this->line("  • {$template->name} ({$template->getTypeLabelAttribute()}) - {$template->getScheduleFrequencyLabelAttribute()}");
        }

        if ($this->option('dry-run')) {
            $this->info('🏃 Dry run completed. Use without --dry-run to actually generate reports.');
            return 0;
        }

        $this->info('📊 Starting report generation...');

        $generated = 0;
        $failed = 0;

        foreach ($toGenerate as $template) {
            try {
                $this->info("📄 Generating: {$template->name}");

                $pdfContent = $template->generateReport();
                $filename = 'scheduled-' . $template->id . '-' . now()->format('Y-m-d-H-i-s') . '.pdf';

                // Save to storage
                \Illuminate\Support\Facades\Storage::disk('local')->put('reports/' . $filename, $pdfContent);

                // Send to recipients
                if (!empty($template->recipients)) {
                    $template->sendToRecipients($pdfContent, $filename);
                    $this->info("  📧 Sent to " . count($template->recipients) . " recipients");
                }

                $this->info("  ✅ Saved as: {$filename}");
                $generated++;

            } catch (\Exception $e) {
                $this->error("  ❌ Failed: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("📈 Generation completed: {$generated} successful, {$failed} failed");

        return $failed > 0 ? 1 : 0;
    }
}
