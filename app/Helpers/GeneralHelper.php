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

function generateTicketNumber(): string
{
    $latestTicket = Ticket::latest('id')->first();
    $lastTicketNumber = $latestTicket ? (int)substr($latestTicket->ticket_no, 1) : 0;
    $newTicketNumber = str_pad($lastTicketNumber + 1, 5, '0', STR_PAD_LEFT);
    return 'T-' . $newTicketNumber;
}
