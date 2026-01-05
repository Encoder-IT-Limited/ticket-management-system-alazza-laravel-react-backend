<?php

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_code' => $this->employee_code,
            'image' => $this->image,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'present_address' => $this->present_address,
            'permanent_address' => $this->permanent_address,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'blood_group' => $this->blood_group,
            'national_id' => $this->national_id,
            'passport_no' => $this->passport_no,
            'department' => $this->department,
            'designation' => $this->designation,
            'supervisor' => $this->supervisor,
            'joining_date' => $this->joining_date,
            'employment_type' => $this->employment_type,
            'status' => $this->status,
            'shift' => $this->shift,
            'basic_salary' => $this->basic_salary,
            'gross_salary' => $this->gross_salary,
            'linkedin' => $this->linkedin ?? null,
            'twitter' => $this->twitter ?? null,
            'facebook' => $this->facebook ?? null,
            'instagram' => $this->instagram ?? null,
            'website' => $this->website ?? null,
        ];
    }
}
