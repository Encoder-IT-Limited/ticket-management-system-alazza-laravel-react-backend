<?php

namespace App\Models\Services;


use App\Mail\TicketCloseMail;
use App\Mail\TicketOpenMail;
use App\Mail\TicketReplyMail;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function ticketOpenMail($ticket): void
    {
//            $users = User::where('role', 'admin')->get();
//            Mail::to($users)->send(new TicketOpenMail($ticket));
        $users = User::where('role', 'admin')->get();
        foreach ($users as $user) {
            Mail::to($user->email)->queue(new TicketOpenMail($ticket, $user));
        }
    }

    public function ticketCloseMail($ticket): void
    {
        $users = User::where('role', 'admin')->get();
        foreach ($users as $user) {
            Mail::to($user->email)->queue(new TicketCloseMail($ticket, $user));
        }
        if ($ticket->client->email) {
            // Mail To user
            Mail::to($ticket->client->email)->queue(new TicketCloseMail($ticket, $ticket->client));
        }
    }

    public function ticketReplyMail($ticket, $reply): void
    {
        if ($reply->to->email) {
            Mail::to($ticket->to->email)
                ->queue(new TicketReplyMail($ticket, $reply));
        }
    }
}
