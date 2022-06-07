<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\HumanResource\Employee\Employee\DeleteRequest;
use App\Http\Requests\HumanResource\Employee\Employee\StoreEmployeeRequest;
use App\Http\Requests\HumanResource\Employee\Employee\UpdateEmployeeRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\CloudStorage;
use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeCompanyEmail;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeGroup;
use App\Model\HumanResource\Employee\EmployeeSalaryHistory;
use App\Model\HumanResource\Employee\EmployeeScorer;
use App\Model\HumanResource\Employee\EmployeeSocialMedia;
use App\Model\Master\Address;
use App\Model\Master\Email;
use App\Model\Master\Phone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $employees = Employee::eloquentFilter($request)
            ->with('group')
            ->with('gender')
            ->with('religion')
            ->with('maritalStatus')
            ->with('companyEmails')
            ->with('socialMedia')
            ->with('contracts')
            ->with('salaryHistories')
            ->with('kpiTemplate')
            ->with('scorers')
            ->with('emails')
            ->with('addresses')
            ->with('phones')
            ->with('status')
            ->with('jobLocation')
            ->with('user');

        $employees = Employee::joins($employees, $request->get('join'));
        
        if ($request->get('scorer_id')) {
            $employees = $employees->whereHas('scorers', function ($q) use ($request) {
                $q->where('user_id', '=', $request->get('scorer_id'))
                    ->orWhere('employees.user_id', $request->get('scorer_id'));
            });
        }

        if ($request->get('is_archived')) {
            $employees = $employees->whereNotNull('archived_at');
        } else {
            $employees = $employees->whereNull('archived_at');
        }

        $employees = pagination($employees, $request->get('limit'));

        $additional = [];
        foreach (explode(',', $request->get('additional')) as $addition) {
            if ($addition == 'groups') {
                $additional = $additional + ['groups' => EmployeeGroup::all()];
            }
        }

        return (new ApiCollection($employees))->additional(['additional' => $additional]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreEmployeeRequest $request
     * @return ApiResource
     */
    public function store(StoreEmployeeRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $employee = new Employee;
        $employee->user_id = $request->get('user_id');
        $employee->code = $request->get('code');
        $employee->name = $request->get('name');
        $employee->personal_identity = $request->get('personal_identity');
        $employee->last_education = $request->get('last_education');
        $employee->birth_date = $request->get('birth_date') ? date('Y-m-d', strtotime($request->get('birth_date'))) : null;
        $employee->birth_place = $request->get('birth_place');
        $employee->employee_gender_id = $request->get('employee_gender_id');
        $employee->employee_marital_status_id = $request->get('employee_marital_status_id');
        $employee->married_with = $request->get('married_with');
        $employee->employee_religion_id = $request->get('employee_religion_id');
        $employee->employee_group_id = $request->get('employee_group_id');
        $employee->join_date = $request->get('join_date') ? date('Y-m-d', strtotime($request->get('join_date'))) : null;
        $employee->job_title = $request->get('job_title');
        $employee->employee_status_id = $request->get('employee_status_id');
        $employee->employee_job_location_id = $request->get('employee_job_location_id');
        $employee->daily_transport_allowance = $request->get('daily_transport_allowance') ?? 0;
        $employee->functional_allowance = $request->get('functional_allowance') ?? 0;
        $employee->communication_allowance = $request->get('communication_allowance') ?? 0;
        $employee->tax_identification_number = $request->get('tax_identification_number');
        $employee->bpjs = $request->get('bpjs');
        $employee->resign_date = $request->get('resign_date') ? date('Y-m-d', strtotime($request->get('resign_date'))) : null;

        $employee->save();

        Address::saveFromRelation($employee, $request->get('addresses'));
        Phone::saveFromRelation($employee, $request->get('phones'));
        Email::saveFromRelation($employee, $request->get('emails'));

        for ($i = 0; $i < count($request->get('company_emails') ?? []); $i++) {
            if ($request->get('company_emails')[$i]['email']) {
                $employeeEmails = new EmployeeCompanyEmail();
                $employeeEmails->employee_id = $employee->id;
                $employeeEmails->email = $request->get('company_emails')[$i]['email'];
                $employeeEmails->save();
            }
        }

        for ($i = 0; $i < count($request->get('salary_histories') ?? []); $i++) {
            $employeeSalaryHistory = new EmployeeSalaryHistory;
            $employeeSalaryHistory->employee_id = $employee->id;
            $employeeSalaryHistory->date = date('Y-m-d', strtotime($request->get('salary_histories')[$i]['date']));
            $employeeSalaryHistory->salary = $request->get('salary_histories')[$i]['salary'];
            $employeeSalaryHistory->save();
        }

        for ($i = 0; $i < count($request->get('social_media') ?? []); $i++) {
            $employeeSocialMedia = new EmployeeSocialMedia;
            $employeeSocialMedia->employee_id = $employee->id;
            $employeeSocialMedia->type = $request->get('social_media')[$i]['type'];
            $employeeSocialMedia->account = $request->get('social_media')[$i]['account'];
            $employeeSocialMedia->save();
        }

        for ($i = 0; $i < count($request->get('contracts') ?? []); $i++) {
            $employeeContract = new EmployeeContract;
            $employeeContract->employee_id = $employee->id;
            $employeeContract->contract_begin = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_begin']));
            $employeeContract->contract_end = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_end']));
            $employeeContract->link = '';
            $employeeContract->notes = $request->get('contracts')[$i]['notes'];
            $employeeContract->save();
        }

        for ($i = 0; $i < count($request->get('attachments') ?? []); $i++) {
            $attachmentId = $request->get('attachments')[$i]['id'];
            $cloudStorage = CloudStorage::findOrFail($attachmentId);
            if ($request->get('attachments')[$i]['key'] == $cloudStorage->key) {
                $cloudStorage->feature_id = $employee->id;
                $cloudStorage->is_user_protected = false;
                $cloudStorage->expired_at = null;
                $cloudStorage->save();
            }
        }

        if ($request->has('scorers')) {
            foreach ($request->get('scorers') as $scorer) {
                if (!$employee->scorers->contains($scorer['id'])) {
                    $employee->scorers()->attach($scorer['id']);
                }
            }
        }

        DB::connection('tenant')->commit();

        return new ApiResource($employee);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int                     $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $employee = Employee::from(Employee::getTableName() . ' as ' . Employee::$alias)->eloquentFilter($request);

        $employee = $employee->with('group')
            ->with('gender')
            ->with('religion')
            ->with('maritalStatus')
            ->with('companyEmails')
            ->with('socialMedia')
            ->with('contracts')
            ->with('salaryHistories')
            ->with('kpiTemplate')
            ->with('scorers')
            ->with('emails')
            ->with('addresses')
            ->with('phones')
            ->with('status')
            ->with('jobLocation')
            ->with('user');

        $employee = Employee::joins($employee, $request->get('join'));

        $employee = $employee->where(Employee::$alias . '.id', $id)->first();

        return new ApiResource($employee);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Employee\Employee\UpdateEmployeeRequest $request
     * @param  int $id
     * @return ApiResource
     */
    public function update(UpdateEmployeeRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $employee = Employee::findOrFail($id);
        $employee->user_id = $request->get('user_id');
        $employee->code = $request->get('code');
        $employee->name = $request->get('name');
        $employee->personal_identity = $request->get('personal_identity');
        $employee->last_education = $request->get('last_education');
        $employee->birth_date = $request->get('birth_date') ? date('Y-m-d', strtotime($request->get('birth_date'))) : null;
        $employee->birth_place = $request->get('birth_place');
        $employee->employee_gender_id = $request->get('employee_gender_id');
        $employee->employee_marital_status_id = $request->get('employee_marital_status_id');
        $employee->married_with = $request->get('married_with');
        $employee->employee_religion_id = $request->get('employee_religion_id');
        $employee->employee_group_id = $request->get('employee_group_id');
        $employee->join_date = $request->get('join_date') ? date('Y-m-d', strtotime($request->get('join_date'))) : null;
        $employee->job_title = $request->get('job_title');
        $employee->employee_status_id = $request->get('employee_status_id');
        $employee->employee_job_location_id = $request->get('employee_job_location_id');
        $employee->daily_transport_allowance = $request->get('daily_transport_allowance');
        $employee->functional_allowance = $request->get('functional_allowance');
        $employee->communication_allowance = $request->get('communication_allowance');
        $employee->tax_identification_number = $request->get('tax_identification_number');
        $employee->bpjs = $request->get('bpjs');
        $employee->resign_date = $request->get('resign_date') ? date('Y-m-d', strtotime($request->get('resign_date'))) : null;

        $employee->save();

        Address::saveFromRelation($employee, $request->get('addresses'));
        Phone::saveFromRelation($employee, $request->get('phones'));
        Email::saveFromRelation($employee, $request->get('emails'));

        for ($i = 0; $i < count($request->get('company_emails') ?? []); $i++) {
            if ($request->get('company_emails')[$i]['email']) {
                $employeeEmails = EmployeeCompanyEmail::first();
                if (!$employeeEmails) {
                    info('here');
                    $employeeEmails = new EmployeeCompanyEmail;
                }
                $employeeEmails->employee_id = $employee->id;
                $employeeEmails->email = $request->get('company_emails')[$i]['email'];
                $employeeEmails->save();
            }
        }

        if ($request->has('salary_histories')) {
            $deleted = array_column($request->get('salary_histories'), 'id');
            EmployeeSalaryHistory::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
            for ($i = 0; $i < count($request->get('salary_histories')); $i++) {
                if (isset($request->get('salary_histories')[$i]['id'])) {
                    $employeeSalaryHistory = EmployeeSalaryHistory::findOrFail($request->get('salary_histories')[$i]['id']);
                } else {
                    $employeeSalaryHistory = new EmployeeSalaryHistory;
                    $employeeSalaryHistory->employee_id = $employee->id;
                }
                $employeeSalaryHistory->date = date('Y-m-d', strtotime($request->get('salary_histories')[$i]['date']));
                $employeeSalaryHistory->salary = $request->get('salary_histories')[$i]['salary'];
                $employeeSalaryHistory->save();
            }
        }

        if ($request->has('social_media')) {
            $deleted = array_column($request->get('social_media'), 'id');
            EmployeeSocialMedia::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
            for ($i = 0; $i < count($request->get('social_media')); $i++) {
                if (isset($request->get('social_media')[$i]['id'])) {
                    $employeeSocialMedia = EmployeeSocialMedia::findOrFail($request->get('social_media')[$i]['id']);
                } else {
                    $employeeSocialMedia = new EmployeeSocialMedia;
                    $employeeSocialMedia->employee_id = $employee->id;
                }
                $employeeSocialMedia->type = $request->get('social_media')[$i]['type'];
                $employeeSocialMedia->account = $request->get('social_media')[$i]['account'];
                $employeeSocialMedia->save();
            }
        }

        if ($request->has('contracts')) {
            $deleted = array_column($request->get('contracts'), 'id');
            EmployeeContract::where('employee_id', $employee->id)->whereNotIn('id', $deleted)->delete();
            for ($i = 0; $i < count($request->get('contracts')); $i++) {
                if (isset($request->get('contracts')[$i]['id'])) {
                    $employeeContract = EmployeeContract::findOrFail($request->get('contracts')[$i]['id']);
                } else {
                    $employeeContract = new EmployeeContract;
                    $employeeContract->employee_id = $employee->id;
                }
                $employeeContract->contract_begin = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_begin']));
                $employeeContract->contract_end = date('Y-m-d', strtotime($request->get('contracts')[$i]['contract_end']));
                $employeeContract->link = '';
                $employeeContract->notes = $request->get('contracts')[$i]['notes'];
                $employeeContract->save();
            }
        }

        if ($request->has('scorers')) {
            $scorers = $request->get('scorers');
            $deleted = array_column($request->get('scorers'), 'id');
            EmployeeScorer::where('employee_id', $employee->id)->whereNotIn('user_id', $deleted)->delete();
            foreach ($scorers as $scorer) {
                if (!$employee->scorers->contains($scorer['id'])) {
                    $employee->scorers()->attach($scorer['id']);
                }
            }
        }

        DB::connection('tenant')->commit();

        return new ApiResource($employee);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteRequest $request
     * @param int $id
     * @return ApiResource
     */
    public function destroy(DeleteRequest $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $employee->delete();

        return new ApiResource($employee);
    }

    /**
     * delete the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(DeleteRequest $request)
    {
        $employees = $request->get('employees');
        foreach ($employees as $employee) {
            $employee = Employee::findOrFail($employee['id']);
            $employee->delete();
        }

        return response()->json([], 204);
    }

    /**
     * Archive the specified resource from storage.
     *
     * @param int $id
     * @return ApiResource
     */
    public function archive($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->archive();

        return new ApiResource($employee);
    }

    /**
     * Archive the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkArchive(Request $request)
    {
        $employees = $request->get('employees');
        foreach ($employees as $employee) {
            $employee = Employee::findOrFail($employee['id']);
            $employee->archive();
        }

        return response()->json([], 200);
    }

    /**
     * Activate the specified resource from storage.
     *
     * @param int $id
     * @return ApiResource
     */
    public function activate($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->activate();

        return new ApiResource($employee);
    }

    /**
     * Archive the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkActivate(Request $request)
    {
        $employees = $request->get('employees');
        foreach ($employees as $employee) {
            $employee = Employee::findOrFail($employee['id']);
            $employee->activate();
        }

        return response()->json([], 200);
    }
}
