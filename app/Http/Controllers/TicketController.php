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
            'line_chart' => $this->generateLineChart(),
            'bar_chart' => $this->generateBarChart(),
        ]);
    }

    private function generateLineChart(): array
    {
        $currentMonth = now()->month;
        $last12Months = collect(range(0, 11))->mapWithKeys(function ($i) use ($currentMonth) {
            $month = ($currentMonth - $i) > 0 ? ($currentMonth - $i) : ($currentMonth - $i + 12);
            return [$month => now()->subMonths($i)->format('M')];
        })->reverse();

        $monthlyStats = Ticket::selectRaw('
        MONTH(created_at) as month,
        COUNT(*) as total_tickets,
        SUM(CASE WHEN is_resolved = 0 THEN 1 ELSE 0 END) as open_tickets,
        SUM(CASE WHEN is_resolved = 1 THEN 1 ELSE 0 END) as closed_tickets,
        SUM(CASE WHEN is_resolved = 1 AND TIMESTAMPDIFF(HOUR, created_at, resolved_at) > 24 THEN 1 ELSE 0 END) as late_resolved_tickets
    ')
            ->whereBetween('created_at', [now()->subMonths(11)->startOfMonth(), now()->endOfMonth()])
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();

        $lineChartData = [];

        foreach ($last12Months as $num => $name) {
            $stats = $monthlyStats->firstWhere('month', $num);

            $lineChartData[] = [
                'name' => $name,
                'openTicket' => $stats->open_tickets ?? 0,
                'closeTicket' => $stats->closed_tickets ?? 0,
                'lateResolvedTicket' => $stats->late_resolved_tickets ?? 0,
            ];
        }

        return [
            'labels' => [
                ['dataKey' => 'openTicket', 'stroke' => '#8884d8'],
                ['dataKey' => 'closeTicket', 'stroke' => '#82ca9d'],
                ['dataKey' => 'lateResolvedTicket', 'stroke' => '#ffc658'],
            ],
            'data' => $lineChartData
        ];
    }

    private function generateBarChart(): array
    {
        $last7Days = collect(range(0, 6))->mapWithKeys(function ($i) {
            return [now()->subDays($i)->format('Y-m-d') => 'Day ' . (7 - $i)];
        })->reverse();

        $weeklyStats = Ticket::selectRaw('
        DATE(created_at) as day,
        COUNT(*) as total_tickets,
        SUM(CASE WHEN is_resolved = 0 THEN 1 ELSE 0 END) as open_tickets,
        SUM(CASE WHEN is_resolved = 1 THEN 1 ELSE 0 END) as closed_tickets,
        SUM(CASE WHEN is_resolved = 1 AND TIMESTAMPDIFF(HOUR, created_at, resolved_at) > 24 THEN 1 ELSE 0 END) as late_resolved_tickets
    ')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupByRaw('DATE(created_at)')
            ->orderByRaw('DATE(created_at)')
            ->get();

        $barChartData = [];

        foreach ($last7Days as $date => $dayLabel) {
            $stats = $weeklyStats->firstWhere('day', $date);

            $barChartData[] = [
                'name' => $dayLabel,
                'openTicket' => $stats->open_tickets ?? 0,
                'closeTicket' => $stats->closed_tickets ?? 0,
                'lateResolvedTicket' => $stats->late_resolved_tickets ?? 0,
            ];
        }

        return [
            'labels' => [
                ['dataKey' => 'openTicket', 'fill' => '#8884d8'],
                ['dataKey' => 'closeTicket', 'fill' => '#82ca9d'],
                ['dataKey' => 'lateResolvedTicket', 'fill' => '#ffc658'],
            ],
            'data' => $barChartData
        ];
    }

    public function review(TicketReviewRequest $request, Ticket $ticket): \Illuminate\Http\JsonResponse
    {
        $ticket = $this->ticketService->createReview($request, $ticket);
        return $this->success('Success', new TicketResource($ticket));
    }

    public function overview(): \Illuminate\Http\JsonResponse
    {
        $ticket = Ticket::where('is_resolved', true)->whereNotNull('rating')->get();

        if ($ticket->isEmpty()) {
            return $this->success('Success', [
//                'very_sad' => 0,
                'sad' => 0,
                'neutral' => 0,
                'happy' => 0,
//                'very_happy' => 0,
                'total' => 0,
//                'happy_clients' => '0%',
            ]);
        }

//        $verySad = $ticket->where('rating', '1')->count();
        $sad = $ticket->where('rating', '1')->count();
        $neutral = $ticket->where('rating', '2')->count();
        $happy = $ticket->where('rating', '3')->count();
//        $veryHappy = $ticket->where('rating', '5')->count();


        $total = $sad + $neutral + $happy;

//        $overPercentageOfHappyClients = ($veryHappy / $total) * 100;

        return $this->success('Success', [
//            'very_sad' => $verySad,
            'sad' => $sad,
            'neutral' => $neutral,
            'happy' => $happy,
//            'very_happy' => $veryHappy,
            'total' => $total,
//            'happy_clients' => $overPercentageOfHappyClients . '%',
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
