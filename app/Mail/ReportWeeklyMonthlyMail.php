<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;

class ReportWeeklyMonthlyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user;
    public $subject;
    public $message;
    public $fileInfo;
    public function __construct($user, $subject, $fileInfo, $message = null)
    {
        $this->subject = $subject;
        $this->fileInfo = $fileInfo;
        $this->user = $user;
        $this->message = $message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reports.report',
            with: [
                'reportUrl' => $this->fileInfo['download_link'],
                'user' => $this->user,
                'message' => $this->message,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->fileInfo['file_path'])
                ->as($this->fileInfo['file_name'])
                ->withMime(match ($this->fileInfo['file_extension']) {
                    'pdf' => 'application/pdf',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'csv' => 'text/csv',
                    default => 'application/octet-stream',
                }),
        ];
    }
}
