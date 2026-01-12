<?php

namespace App\Models\Services;

use App\Models\Employee;

class EmployeeService
{
    public function getAll()
    {
        $params = request()->all();
        $per_page = request()->get('per_page', 10);

        # check user role and filter data accordingly
        $user = auth()->user();
        if ($user->role->name != 'Admin') {
            $params['email'] = $user->email;
        }

        $data = Employee::orderBy('id', 'desc');
        $data = $this->filter($data, $params);
        return $data->paginate($per_page);
    }

    private function filter($data, $pamarms)
    {
        # search
        if (isset($pamarms['search']) && $pamarms['search'] != '') {
            $data = $data->where(function ($query) use ($pamarms) {
                $query->where('name', 'like', '%' . $pamarms['search'] . '%')
                    ->orWhere('employee_code', 'like', '%' . $pamarms['search'] . '%')
                    ->orWhere('email', 'like', '%' . $pamarms['search'] . '%')
                    ->orWhere('phone', 'like', '%' . $pamarms['search'] . '%');
            });
        }
        # search designation
        if (isset($pamarms['designation']) && $pamarms['designation'] != '') {
            $data = $data->where('designation', $pamarms['designation']);
        }
        # search department
        if (isset($pamarms['department']) && $pamarms['department'] != '') {
            $data = $data->where('department', $pamarms['department']);
        }
        # status
        if (isset($pamarms['status']) && $pamarms['status'] != '') {
            $data = $data->where('status', $pamarms['status']);
        }
        # employment_type
        if (isset($pamarms['employment_type']) && $pamarms['employment_type'] != '') {
            $data = $data->where('employment_type', $pamarms['employment_type']);
        }
        # email filter
        if (isset($pamarms['email']) && $pamarms['email'] != '') {
            $data = $data->where('email', $pamarms['email']);
        }
        return $data;
    }

    public function store($request)
    {
        return Employee::create($this->processData($request, null));
    }

    public function update($request, $employee)
    {
        $employee->update($this->processData($request, $employee->image));
        return $employee;
    }

    private function processData($request, $oldImage)
    {
        $data = $request->validated();
        # Handle image upload
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = updateFileV1(
                $request->file('image'),
                $oldImage,
                'employees'
            );
        } elseif ($oldImage && !$request->has('image')) {
            deleteFile($oldImage);
            $data['image'] = null;
        }
        # Generate employee code if not provided
        if (!isset($data['employee_code']) || !$data['employee_code']) {
            $data['employee_code'] = 'EMP-' . strtoupper(uniqid());
        }

        return $data;
    }

    public function destroy($employee)
    {
        // Delete associated image
        if ($employee->image) {
            deleteFile($employee->image);
        }
        $employee->delete();
    }
}
