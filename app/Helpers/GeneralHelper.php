<?php

use App\Models\Ticket;

function perPage($perPage = 15)
{
    $perPage = request('per_page', $perPage);
    if ($perPage < 1) $perPage = 100000000; // get all data
    return $perPage;
}

function formatDateTime($dateTime): string
{
    return $dateTime->format('Y-m-d H:i:s');
}

function remove_($value): array|string
{
    return str_replace('_', ' ', $value);
}

function failureResponse($message, $status = 400): \Illuminate\Http\JsonResponse
{
    return response()->json(['success' => false, 'message' => $message, 'status' => $status], $status);
}

function generateTicketNumber($add = 0): string
{
    $latestTicket = Ticket::latest('id')->first();

    if (!$latestTicket) {
        return 'T-0001';
    }

    $latestTicketNo = $latestTicket->ticket_no;
    $latestTicketNo = substr($latestTicketNo, 2);
    $latestTicketNo = (int)$latestTicketNo + 1 + $add;
    $latestTicketNo = str_pad($latestTicketNo, 4, '0', STR_PAD_LEFT);

    if (Ticket::where('ticket_no', 'T-' . $latestTicketNo)->exists()) {
        return generateTicketNumber(1);
    }

    return 'T-' . $latestTicketNo;
}
