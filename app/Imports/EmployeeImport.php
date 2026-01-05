<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Throwable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\ToArray;

class EmployeeImport implements SkipsEmptyRows, SkipsOnFailure, ToArray, WithHeadingRow, WithValidation
{
    use ApiResponseTrait, Importable, SkipsErrors, SkipsFailures;

    public $insertData = [];

    public function array(array $rows)
    {
        // foreach ($rows as $key => $row) {
        //     $this->insertData[] = [
        //         'employee_code' => $row['employee_code'] ?? 'EMP-' . strtoupper(uniqid()),
        //         'image' => $row['image'] ?? null,
        //         'name' => $row['name'] ?? null,
        //         'father_name' => $row['father_name'] ?? null,
        //         'mother_name' => $row['mother_name'] ?? null,
        //         'email' => $row['email'] ?? null,
        //         'phone' => $row['phone'] ?? null,
        //         'emergency_contact_name' => $row['emergency_contact_name'] ?? null,
        //         'emergency_contact_phone' => $row['emergency_contact_phone'] ?? null,
        //         'present_address' => $row['present_address'] ?? null,
        //         'permanent_address' => $row['permanent_address'] ?? null,
        //         'gender' => $row['gender'] ?? null,
        //         'date_of_birth' => $row['date_of_birth'] ?? null,
        //         'blood_group' => $row['blood_group'] ?? null,
        //         'national_id' => $row['national_id'] ?? null,
        //         'passport_no' => $row['passport_no'] ?? null,
        //         'department' => $row['department'] ?? null,
        //         'designation' => $row['designation'] ?? null,
        //         'supervisor' => $row['supervisor'] ?? null,
        //         'joining_date' => isset($row['joining_date']) && $row['joining_date'] ? date('Y-m-d', strtotime($row['joining_date'])) : null,
        //         'employment_type' => $row['employment_type'] ?? 'full_time',
        //         'status' => $row['status'] ?? 'active',
        //         'shift' => $row['shift'] ?? null,
        //         'basic_salary' => $row['basic_salary'] ?? 0,
        //         'gross_salary' => $row['gross_salary'] ?? 0,
        //     ];
        // }
        $this->insertData = $rows;
    }

    public function getArray()
    {
        return $this->insertData;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            'employee_code' => 'nullable|distinct|unique:employees,employee_code',
            'image' => 'nullable',
            'first_name' => 'required|string',
            'last_name' => 'nullable|string',
            'father_name' => 'nullable|string',
            'mother_name' => 'nullable|string',

            'email' => 'required|email|distinct|unique:employees,email',
            'phone' => 'nullable|distinct|unique:employees,phone',
            'work_phone' => 'nullable',
            'position' => 'nullable',

            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable',

            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',

            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'blood_group' => 'nullable|string',

            'national_id' => 'nullable|distinct|unique:employees,national_id',
            'passport_no' => 'nullable|distinct|unique:employees,passport_no',

            'department' => 'nullable|string',
            'designation' => 'nullable|string',
            'supervisor' => 'nullable|string',

            'joining_date' => 'nullable|date',
            'employment_type' => 'nullable|string|in:full_time,part_time,contract,intern',
            'status' => 'nullable|string|in:active,inactive,terminated,resigned',
            'shift' => 'nullable|string',

            'basic_salary' => 'nullable|numeric',
            'gross_salary' => 'nullable|numeric',

            'linkedin' => 'nullable|url',
            // 'twitter' => 'nullable|url',
            // 'facebook' => 'nullable|url',
            // 'instagram' => 'nullable|url',
            'website' => 'nullable|url',
        ];
    }

    public function onError(Throwable $e)
    {
        Log::error('Error during import: ' . $e->getMessage());
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
