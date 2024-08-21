<?php

namespace App\Models\Services;

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
            ->with('client', 'admin');

        if (auth()->user()->role !== 'admin') {
            $data->where('client_id', auth()->id());
        }
        if (request('start_date') && request('end_date')) {
            $from = Carbon::parse(request('start_date'));
            $to = Carbon::parse(request('end_date'));
            $data->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to);
        }

        $data = $data->latest()->paginate(perPage(25));
        return $data;
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
            'title', 'description',
            'status',
            'client.name',
            'admin.name',
            'is_resolved',
            'created_at',
            'resolved_at',

        ];
        $headers = [
            'Title', 'Description',
            'Status',
            'Client Name',
            'Admin Name',
            'Is Resolved',
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
}
