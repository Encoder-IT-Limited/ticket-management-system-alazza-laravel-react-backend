<?php

namespace App\Models\Services;

use App\Http\Resources\Car\CarCollection;
use App\Http\Resources\Car\CarResource;
use App\Models\Car;
use App\Models\CarBrand;

class CarService
{

    public function getAll()
    {
        $query = request('search_query');
        return Car::whereAny(['title', 'emails', 'plate_number_arabic', 'plate_number_english', 'vehicle_identification_no']
            , 'like'
            , "%$query%")
            ->with('media', 'carBrand')
            ->latest()
            ->paginate(request('per_page', 25));
    }

    public function search()
    {
        $query = request('search_query');
        return Car::whereAny(['title', 'plate_number_arabic', 'plate_number_english', 'vehicle_identification_no'], 'like', "%$query%")
            ->with('carBrand')
            ->latest()
            ->get();
    }

    public function store($request)
    {
        $data = $request->validated();
        // Convert date strings to proper format
        $registration_expiry = date_create_from_format('d/m/Y', $data['registration_expiry']);
        $inspection_expiry = date_create_from_format('d/m/Y', $data['inspection_expiry']);
        $insurance_expiry = date_create_from_format('d/m/Y', $data['insurance_expiry']);

        // Check if date parsing failed
        if ($registration_expiry === false || $inspection_expiry === false || $insurance_expiry === false) {
            $registration_expiry = null;
            $inspection_expiry = null;
            $insurance_expiry = null;
        } else {
            // Replace date strings with properly formatted dates
            $data['registration_expiry'] = $registration_expiry->format('Y-m-d');
            $data['inspection_expiry'] = $inspection_expiry->format('Y-m-d');
            $data['insurance_expiry'] = $insurance_expiry->format('Y-m-d');
        }

        if (isset($data['brand'])) {
            $data['car_brand_id'] = CarBrand::firstOrCreate(['name' => strtolower($data['brand'])])->id;
        }

        $car = new Car();
        $car->fill($data);
        $car->save();
        $this->uploadFiles($request, $car);
        $car->load('media');

        return $car;
    }

    public function update($request, $car)
    {
        $data = $request->validated();
        // Convert date strings to proper format
        $registration_expiry = date_create_from_format('d/m/Y', $data['registration_expiry']);
        $inspection_expiry = date_create_from_format('d/m/Y', $data['inspection_expiry']);
        $insurance_expiry = date_create_from_format('d/m/Y', $data['insurance_expiry']);

        // Check if date parsing failed
        if ($registration_expiry === false || $inspection_expiry === false || $insurance_expiry === false) {
            $registration_expiry = null;
            $inspection_expiry = null;
            $insurance_expiry = null;
        } else {
            // Replace date strings with properly formatted dates
            $data['registration_expiry'] = $registration_expiry->format('Y-m-d');
            $data['inspection_expiry'] = $inspection_expiry->format('Y-m-d');
            $data['insurance_expiry'] = $insurance_expiry->format('Y-m-d');
        }

        $data['registration_status'] = "unsent";
        $data['inspection_status'] = "unsent";
        $data['insurance_status'] = "unsent";

        if (isset($data['brand'])) {
            $data['car_brand_id'] = CarBrand::firstOrCreate(['name' => strtolower($data['brand'])])->id;
        }

        $car->fill($data);
        $car->save();
        $car->refresh();
        $this->uploadFiles($request, $car);
        $car->load('media');

        return new CarResource($car);
    }

    protected function uploadFiles($request, $model): void
    {
        if ($request->has('vehicle_photos')) {
            foreach ($request->vehicle_photos as $key => $document) {
                $model->uploadMedia($document, 'vehicle_photos_' . $key, 'vehicle_photos');
            }
        }
        if ($request->has('vehicle_registration')) {
            foreach ($request->vehicle_registration as $key => $document) {
                $model->uploadMedia($document, 'vehicle_registration_' . $key, 'vehicle_registration');
            }
        }
        if ($request->has('vehicle_delivery')) {
            foreach ($request->vehicle_delivery as $key => $document) {
                $model->uploadMedia($document, 'vehicle_delivery_' . $key, 'vehicle_delivery');
            }
        }
        if ($request->has('vehicle_clearance')) {
            foreach ($request->vehicle_clearance as $key => $document) {
                $model->uploadMedia($document, 'vehicle_clearance_' . $key, 'vehicle_clearance');
            }
        }
        if ($request->has('vehicle_user_acknowledgement')) {
            foreach ($request->vehicle_user_acknowledgement as $key => $document) {
                $model->uploadMedia($document, 'vehicle_user_acknowledgement_' . $key, 'vehicle_user_acknowledgement');
            }
        }
        if ($request->has('insurance')) {
            foreach ($request->insurance as $key => $document) {
                $model->uploadMedia($document, 'insurance_' . $key, 'insurance');
            }
        }
        if ($request->has('others')) {
            foreach ($request->others as $key => $document) {
                $model->uploadMedia($document, 'others_' . $key, 'others');
            }
        }
        if ($request->has('photos')) {
            foreach ($request->photos as $key => $document) {
                $model->uploadMedia($document, 'photos_' . $key, 'photos');
            }
        }
    }
}
