<?php

namespace App\Models\Services;

use App\Http\Resources\Car\CarCollection;
use App\Http\Resources\Car\CarResource;
use App\Http\Resources\CarMaintenanceCollection;
use App\Models\Car;
use App\Models\CarMaintenance;

class CarMaintenanceService
{

    public function getAll()
    {
        $query = request('search_query');
        $carId = request('car_id');
        $dateRange = request('date_range'); // e.g. 2020-01-01 - 2020-01-31 // Must be separated by space and -
        $carMaintenances = CarMaintenance::query();
        $carMaintenances->with('car', 'media');
        if ($query) {
            $carMaintenances->search($query, [
                '%name', '%mobile', '%company_name', '%car_location', '%maintenance_location',
                'car|%plate_number_arabic,%plate_number_english,%title',
//                'car|%plate_number_english',
//                'car|%title',
                'car.carBrand|%name',
            ]);
        }
        if ($dateRange) {
            $carMaintenances->searchDate($dateRange, ['maintenance_date'], '><');
        }
        if ($carId) {
            $carMaintenances->where('car_id', $carId);
        }
        return $carMaintenances->latest()->paginate(request('per_page', 25));
    }

    public function store($request): CarMaintenance
    {
        $data = $request->validated();
        if (isset($data['car_id'])) {
            $car = Car::findOrFail($data['car_id']);
            $car->email_sent = false;
            $car->save();
            if ((!isset($data['name']) || !$data['name'])) {
                $data['name'] = $car->title;
            }
        }
        $carMaintenance = new CarMaintenance();
        $carMaintenance->fill($data);
        $carMaintenance->save();
        $this->uploadFiles($request, $carMaintenance);
        $carMaintenance->load('media');

        return $carMaintenance;
    }

    public function update($request, $carMaintenance)
    {
        $data = $request->validated();
        unset($data['plate_number_arabic'], $data['plate_number_english']);
        if ($carMaintenance->car_id !== $data['car_id']) {
            $car = Car::findOrFail($data['car_id']);
            $car->email_sent = false;
            $car->save();
//            $data['name'] = $car->title;
            $data['plate_number_arabic'] = $car->plate_number_arabic;
            $data['plate_number_english'] = $car->plate_number_english;
        }
        $carMaintenance->fill($data);
        $carMaintenance->save();
        $carMaintenance->refresh();
        $this->uploadFiles($request, $carMaintenance);
        $carMaintenance->load('media');

        return $carMaintenance;
    }

    protected function uploadFiles($request, $model): void
    {
        if ($request->has('attachments')) {
            foreach ($request->attachments as $key => $document) {
                $model->uploadMedia($document, 'car_maintenance_attachments_' . $key, 'maintenance_attachments');
            }
        }
    }
}
