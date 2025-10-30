<?php

namespace App\Models\Services;


use App\Mail\EmailVerificationMail;
use App\Mail\TicketCloseMail;
use App\Mail\TicketOpenMail;
use App\Mail\TicketReplyMail;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MailService
{
    public function ticketOpenMail($ticket): void
    {
        $emails = User
            ::whereHas('permissions', function ($q) {
                $q->where('slug', 'ticket-open-mail');
            })
            ->pluck('email')
            ->toArray();

        foreach ($emails as $email) {
            Mail::to($email)->queue(
                new TicketOpenMail(
                    $ticket,
                    $email
                )
            );
        }
    }

    public function ticketCloseMail($ticket): void
    {
        $emails = User
            ::whereHas('permissions', function ($q) {
                $q->where('slug', 'ticket-close-mail');
            })
            ->pluck('email')
            ->toArray();

        foreach ($emails as $email) {
            Mail::to($email)->queue(
                new TicketCloseMail(
                    $ticket,
                    $email
                )
            );
        }

        if ($ticket->client && $ticket->client->email) {
            // Mail To user
            Mail::to($ticket->client->email)->send(
                new TicketCloseMail(
                    $ticket,
                    $ticket->client
                )
            );
        }
    }

    public function ticketReplyMail($ticket, $reply): void
    {
        if ($reply->to && $reply->to->email) {
            Mail::to($reply->to->email)->queue(
                new TicketReplyMail($ticket, $reply)
            );
        }
    }

    public function sendEmailVerificationMail($user): void
    {
        if ($user && $user->email_verified_at === null) {
            EmailVerificationToken::where('email', $user->email)->delete();
            $emailToken = EmailVerificationToken::create([
                'email' => $user->email,
                'token' => $token = sha1(time() . Str::random(10)),
            ]);
            Mail::to($user->email)->queue(new EmailVerificationMail($user, $emailToken));
        }
    }
}
