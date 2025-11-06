<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function newTickets(): \Illuminate\Http\JsonResponse
    {
        $query = Ticket::where('admin_id', null)->where('is_resolved', false);

        $category_ids = auth()->user()->role->getCategoryIds();
        if (count($category_ids) > 0) {
            $query->whereIn('category_id', $category_ids);
        }

        $ticketCount = $query->count();

        return $this->success('Success', [
            'ticket_count' => $ticketCount
        ]);
    }
}
