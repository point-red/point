<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiResource;
use App\Services\EmployeeArchieveService;
use Illuminate\Http\Request;

class EmployeeArchiveController extends Controller
{
    public function index(Request $request, EmployeeArchieveService $service) {
        $archives = $service->findArchiveName();
        return new ApiResource($archives);
    }

    public function show(Request $request, EmployeeArchieveService $service) {
        $archiveDate=$request->get('archive');
        $search=$request->get('search');
        $employess = $service->findEmployeeByArchieveDate($archiveDate, $search);
        return new ApiResource($employess);
    }

    public function store(Request $request, EmployeeArchieveService $service) {
        $employee=$request->post('employee');
        $employeeIds = $service->create(explode(',', $employee));
        return new ApiResource($employeeIds);
    }
}
