<?php

namespace App\Mail;

use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public EmailVerificationToken $emailToken;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, EmailVerificationToken $emailToken)
    {
        $this->user = $user;
        $this->emailToken = $emailToken;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Verification Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.EmailVerificationMail',
            with: [
                'user' => $this->user,
                'emailToken' => $this->emailToken,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
