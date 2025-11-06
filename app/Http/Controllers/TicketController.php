<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Http\Requests\TicketReviewRequest;
use App\Http\Resources\Ticket\TicketCollection;
use App\Http\Resources\Ticket\TicketResource;
use App\Models\Services\MailService;
use App\Models\Services\TicketService;
use App\Models\Ticket;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\CauserResolver;

class TicketController extends Controller
{
    use ApiResponseTrait, CommonTrait;

    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $tickets = $this->ticketService->getAll();
        return $this->success('Success', TicketCollection::make($tickets));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TicketRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $ticket = $this->ticketService->store($request);
            $ticket->load('client', 'media');

            // Send Email to All Admin
            $mail = new MailService();
            $mail->ticketOpenMail($ticket);

            return $this->success('Ticket created successfully', new TicketResource($ticket));
        } catch (\Exception $e) {
            return $this->failure($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        $ticket->load(['client', 'admin', 'media', 'ticketReplies' =>
        function ($query) {
            $query->with('from', 'to', 'media')
                ->orderBy('created_at', request('direction', 'asc'));
        }]);
        return $this->success('Success', new TicketResource($ticket));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TicketRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        if ($ticket->is_resolved == 1) {
            return $this->failure('Ticket already closed! Cannot Update Ticket', 400);
        }

        $is_resolved = $ticket->is_resolved;
        $ticket = $this->ticketService->update($request, $ticket);
        $ticket->refresh();
        $ticket->load('client', 'admin', 'media');

        // Send Email ... if ticket is resolved (changed from 0 to 1)
        if ($is_resolved == 0 && $ticket->is_resolved == 1) {
            $mail = new MailService();
            $mail->ticketCloseMail($ticket);
        }
        return $this->success('Ticket updated successfully', new TicketResource($ticket));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        // logged in user is not admin or ticket is assigned to another admin
        if ((auth()->user()->role !== 'admin') || ($ticket->admin_id && $ticket->admin_id !== auth()->id())) {
            return $this->failure('You are not authorized to perform this action', 403);
        }
        CauserResolver::setCauser(auth()->user());
        $ticket->delete();
        return $this->success('Ticket deleted successfully');
    }

    public function resolved(Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        //        if ((auth()->user()->role !== 'admin')) {
        //            return $this->failure('You are not authorized to perform this action', 403);
        //        }
        //        if ($ticket->is_resolved == 1) {
        //            return $this->failure('Ticket already closed', 400);
        //        }
        $this->ticketService->resolved($ticket);
        $mail = new MailService();
        $mail->ticketCloseMail($ticket);
        return $this->success('Ticket resolved successfully');
    }

    public function statistics(): \Illuminate\Http\JsonResponse
    {
        $ticketCounts = Ticket::selectRaw('
        COUNT(*) as ticket_count,
        SUM(CASE WHEN is_resolved = 0 THEN 1 ELSE 0 END) as open_ticket_count,
        SUM(CASE WHEN is_resolved = 1 THEN 1 ELSE 0 END) as closed_ticket_count,
        SUM(CASE WHEN is_resolved = 1 AND TIMESTAMPDIFF(HOUR, created_at, resolved_at) > 24 THEN 1 ELSE 0 END) as late_resolved_count
    ')->first();

        return $this->success('Success', [
            'ticket_count' => $ticketCounts->ticket_count,
            'open_ticket_count' => $ticketCounts->open_ticket_count,
            'closed_ticket_count' => $ticketCounts->closed_ticket_count,
            'late_resolved_count' => $ticketCounts->late_resolved_count,
            'line_chart' => $this->ticketService->generateLineChart(),
            'bar_chart' => $this->ticketService->generateBarChart(),
        ]);
    }

    public function review(TicketReviewRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        $ticket = $this->ticketService->createReview($request, $ticket);
        return $this->success('Success', new TicketResource($ticket));
    }

    public function overview(): \Illuminate\Http\JsonResponse
    {
        $query = Ticket::where('is_resolved', true)->whereNotNull('rating');

        $category_ids = auth()->user()?->role?->getCategoryIds();
        if ($category_ids && count($category_ids) > 0) {
            $query->whereIn('category_id', $category_ids);
        }

        $tickets = $query->get();
        if ($tickets->isEmpty()) {
            return $this->success('Success', [
                'sad' => 0,
                'neutral' => 0,
                'happy' => 0,
                'total' => 0,
            ]);
        }

        $sad = $query->where('rating', '1')->count();
        $neutral = $query->where('rating', '2')->count();
        $happy = $query->where('rating', '3')->count();
        $total = $sad + $neutral + $happy;

        return $this->success('Success', [
            'sad' => $sad,
            'neutral' => $neutral,
            'happy' => $happy,
            'total' => $total,
        ]);
    }

    public function export(Request $request): \Illuminate\Http\Response|string|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        if ((auth()->user()->role !== 'admin')) {
            return $this->failure('You are not authorized to perform this action', 403);
        }
        return $this->ticketService->export($request);
    }
}
