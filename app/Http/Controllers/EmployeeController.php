<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\Employee\EmployeeCollection;
use App\Http\Resources\Employee\EmployeeResource;
use App\Imports\EmployeeImport;
use App\Models\Employee;
use App\Models\Services\EmployeeService;
use App\Traits\ApiResponseTrait;
use App\Traits\CommonTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    use ApiResponseTrait, CommonTrait;

    protected $service;

    public function __construct(EmployeeService $service)
    {
        $this->service = $service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = $this->service->getAll();
        return $this->success('Employees retrieved successfully', EmployeeCollection::make($employees));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        try {
            $employee = $this->service->store($request);
            return $this->success('Employee created successfully', new EmployeeResource($employee));
        } catch (\Exception $e) {
            return $this->failure($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        return $this->success('Employee retrieved successfully', new EmployeeResource($employee));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        try {
            $employee = $this->service->update($request, $employee);
            return $this->success('Employee updated successfully', new EmployeeResource($employee));
        } catch (\Exception $e) {
            return $this->failure($e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        try {
            $this->service->destroy($employee);
            return $this->success('Employee deleted successfully');
        } catch (\Exception $e) {
            return $this->failure($e->getMessage(), 500);
        }
    }

    public function import(Request $request)
    {

        try {
            $file = $request->file('file');
            if (!$file) {
                return $this->failure('No file uploaded', 422);
            }
            $import = new EmployeeImport();
            Excel::import($import, $file);

            if ($import->failures()->isNotEmpty()) {
                return $this->failure($import->failures(), 422);
            }
            return $this->success('Employee imported successfully');
        } catch (\Exception $e) {
            return $this->failure('Error importing Employee', 500, $e->getMessage());
        }
    }
}
