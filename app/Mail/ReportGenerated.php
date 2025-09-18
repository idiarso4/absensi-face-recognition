<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ReportGenerated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $reportType;
    public string $pdfContent;
    public string $filename;

    /**
     * Create a new message instance.
     */
    public function __construct(string $reportType, string $pdfContent, string $filename, array $metadata = [])
    {
        $this->reportType = $reportType;
        $this->pdfContent = $pdfContent;
        $this->filename = $filename;
        $this->metadata = $metadata;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $reportTitles = [
            'attendance' => 'Laporan Absensi',
            'leave' => 'Laporan Cuti/Izin',
            'monthly-summary' => 'Ringkasan Bulanan Absensi'
        ];

        $subject = ($reportTitles[$this->reportType] ?? 'Laporan') . ' - ' . now()->format('d F Y');

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.report-generated',
            with: [
                'reportType' => $this->reportType,
                'filename' => $this->filename,
                'metadata' => $this->metadata,
                'generatedAt' => now(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->filename)
                ->withMime('application/pdf'),
        ];
    }
}
