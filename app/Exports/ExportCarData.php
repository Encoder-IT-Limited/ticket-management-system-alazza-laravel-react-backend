<?php

namespace App\Exports;

use App\Models\Car;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;

class ExportCarData implements FromCollection, WithHeadings, WithStyles
{
    use Exportable;

    protected $ids;

    public function __construct($ids)
    {
        $this->ids = $ids;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Car::whereIn('id', $this->ids)->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'id',
            'title',
            'registration_expiry',
            'inspection_expiry',
            'insurance_expiry',
            'emails',
            'registration_status',
            'inspection_status',
            'insurance_status',
            'plate_number_arabic',
            'plate_number_english',
            'vehicle_identification_no',
            'vehicle_color',
            'year',
            'sadad_serial_number',
            'owner_in_registration',
            'company',
            'assigned_driver_arabic',
            'assigned_driver_english',
            'driver_id',
            'vehicle_registration_validity',
            'mvpi_validity',
            'insurance_company',
            'remark',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ]
            ],
        ];
    }
}
