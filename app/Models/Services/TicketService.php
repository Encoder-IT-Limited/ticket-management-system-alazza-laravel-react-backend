<?php

namespace App\Models\Services;

use App\Models\Category;
use App\Models\Ticket;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TicketService
{
    use CommonTrait;

    public function getAll(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = request('search_query');
        $data = Ticket::query();
        $data->whereAny(['title', 'description', 'ticket_no'], 'like', "%$query%")
            ->with('client', 'admin', 'category', 'category.parent.parent');

        if (auth()->user()->role->name == 'Client') {
            $data->where('client_id', auth()->id());
        }
        if (request('start_date') && request('end_date')) {
            $from = Carbon::parse(request('start_date'));
            $to = Carbon::parse(request('end_date'));
            $data->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to);
        }
        if (request('category_id')) {
            $categoryId = (int) request('category_id');
            $descendantIds = Category::getDescendantIds($categoryId);
            $idsToMatch = array_unique(array_merge([$categoryId], $descendantIds));
            $data->whereIn('category_id', $idsToMatch);
        }
        if (request('priority')) {
            $data->where('priority', request('priority'));
        }
        if (request('status')) {
            $data->where('status', request('status'));
        }

        $category_ids = auth()->user()->role->getCategoryIds();
        if (count($category_ids) > 0) {
            $data->whereIn('category_id', $category_ids);
        }

        return $data->latest()->paginate(perPage(25));
    }

    public function store($request): Ticket
    {
        $data = $request->validated();
        $data['client_id'] = auth()->id();
        $data['ticket_no'] = generateTicketNumber();
        $ticket = new Ticket();
        $ticket->fill($data);
        $ticket->save();
        $this->uploadFiles($request, $ticket);

        return $ticket;
    }

    public function update($request, $ticket)
    {
        $data = $request->validated();
        if (isset($data['is_resolved'])) {
            $data['resolved_at'] = $data['is_resolved'] ? now() : null;
            $data['status'] = $data['is_resolved'] ? 'closed' : 'open';
            //            $data['admin_id'] = $data['is_resolved'] ? auth()->id() : null;
            $data['is_resolved'] = $data['is_resolved'] ? 1 : 0;
        }
        $ticket->fill($data);
        $ticket->save();
        $this->uploadFiles($request, $ticket);

        return $ticket;
    }

    public function resolved($ticket): void
    {
        $ticket->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            //            'admin_id' => auth()->user()->role === 'admin' ? auth()->id() : null,
            'status' => 'closed',
            'resolved_by' => auth()->id(),
        ]);
    }

    public function createReview($request, $ticket)
    {
        $data = $request->validated();
        $ticket->update($data);
        return $ticket;
    }


    protected function uploadFiles($request, $model): void
    {
        if ($request->has('files')) {
            foreach ($request->files as $key => $document) {
                foreach ($document as $file) {
                    $model->uploadMedia($file, $model?->client?->name . '_' . $key, 'ticket_files');
                }
            }
        }
    }

    public function export(Request $request): \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse|string
    {
        $request->validate([
            'ids' => 'sometimes|required|array',
            'format' => 'sometimes|required|in:excel,xlsx,csv,pdf',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date',
        ]);

        $columns = [
            'title',
            'description',
            'status',
            'client.name',
            'client.company',
            'admin.name',
            'is_resolved',
            'rating',
            'review',
            'created_at',
            'resolved_at',

        ];
        $headers = [
            'Title',
            'Description',
            'Status',
            'Client Name',
            'Company Name',
            'Admin Name',
            'Is Resolved',
            'Rating',
            'Review',
            'Created At',
            'Resolved At',
        ];

        $data = Ticket::query();
        if ($request->has('ids')) {
            $data->whereIn('id', $request->ids)->get();
        }
        if (request('start_date') && request('end_date')) {
            $from = Carbon::parse(request('start_date'));
            $to = Carbon::parse(request('end_date'));
            $data->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to);
        }

        $data = $data->with('client', 'admin')->get();

        return $this->exportData(null, $columns, $headers, 'tickets', $data);
    }

    public function processReport($start, $end, $isWeekly = true)
    {
        $columns = [
            'title',
            'description',
            'status',
            'client.name',
            'client.company',
            'admin.name',
            'is_resolved',
            'rating',
            'review',
            'created_at',
            'resolved_at',

        ];
        $headers = [
            'Title',
            'Description',
            'Status',
            'Client Name',
            'Company Name',
            'Admin Name',
            'Is Resolved',
            'Rating',
            'Review',
            'Created At',
            'Resolved At',
        ];

        $data = Ticket::whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
        $data = $data->with('client', 'admin')->get();

        $fileName = 'reports_' . ($isWeekly ? 'weekly' : 'monthly') . '_' . $start . '_to_' . $end;

        return $this->exportFileStore(null, $columns, $headers, $fileName, $data);
    }


    public function generateLineChart(): array
    {
        $currentMonth = now()->month;
        $last12Months = collect(range(0, 11))->mapWithKeys(function ($i) use ($currentMonth) {
            $month = ($currentMonth - $i) > 0 ? ($currentMonth - $i) : ($currentMonth - $i + 12);
            return [$month => now()->subMonths($i)->format('M Y')];
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
                'Name' => $name,
                'Open Ticket' => (int)($stats->open_tickets ?? 0),
                'Close Ticket' => (int)($stats->closed_tickets ?? 0),
                'Late Ticket' => (int)($stats->late_resolved_tickets ?? 0),
            ];
        }

        return [
            'labels' => [
                ['dataKey' => 'Open Ticket', 'stroke' => '#008000'],
                ['dataKey' => 'Close Ticket', 'stroke' => '#FFA500'],
                ['dataKey' => 'Late Ticket', 'stroke' => '#FF0000'],
            ],
            'data' => $lineChartData
        ];
    }

    public function generateBarChart(): array
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
                'Name' => $date,
                'Open Ticket' => (int)($stats->open_tickets ?? 0),
                'Close Ticket' => (int)($stats->closed_tickets ?? 0),
                'Late Ticket' => (int)($stats->late_resolved_tickets ?? 0),
            ];
        }

        return [
            'labels' => [
                ['dataKey' => 'Open Ticket', 'fill' => '#8884d8'],
                ['dataKey' => 'Close Ticket', 'fill' => '#82ca9d'],
                ['dataKey' => 'Late Ticket', 'fill' => '#ffc658'],
            ],
            'data' => $barChartData
        ];
    }
}
