<?php

namespace App\Traits;

use App\Exports\DataExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel;

trait CommonTrait
{
    public function exportData($model, $columns, $head, $fileName = 'export_data', $modelDatas = null)
    {
        if (gettype(request('ids')) == 'string') {
            $ids = explode(',', \request('ids'));
        } else if (gettype(request('ids')) == 'array') {
            $ids = \request('ids');
        } else {
            $ids = null;
        }

        $format = \request('format', 'excel');

        $path = '';
        if ($format === 'excel' || $format === 'xlsx') {
            $path = 'exports/' . $fileName . '_' . time() . '.xlsx';
            return (new DataExporter($ids, $model, $columns, $head, $modelDatas))
                ->download($fileName . '.xlsx', Excel::XLSX, ['X-Vapor-Base64-Encode' => 'True']);
//                ->store($path, 'public', Excel::XLSX);
        }
        if ($format === 'csv') {
            $path = 'exports/' . $fileName . '_' . time() . '.csv';
            return (new DataExporter($ids, $model, $columns, $head, $modelDatas))
                ->download($fileName . '.csv', Excel::CSV, ['Content-Type' => 'text/csv',]);
//                ->store($path, 'public', Excel::CSV);
        }
        if ($format === 'pdf') {
            $path = 'exports/' . $fileName . '_' . time() . '.pdf';
            return (new DataExporter($ids, $model, $columns, $head, $modelDatas))
                ->download($fileName . '.pdf', Excel::DOMPDF);
        }
        return $path;
    }

}
