<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Master\User;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationTarget;

class KpiAutomatedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiResult\KpiResultCollection
     */
    public function index(Request $request)
    {
        $returnable = [];

        if (count($request->automated_ids) > 0 && $request->date)
        {
            foreach ($request->automated_ids as $automated_id)
            {
                $returnable[$automated_id] = $this->getAutomatedData($automated_id, $request->date, $request->employeeId);
            }
        }

        return $returnable;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getAutomatedData($automated_id, $date, $employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $userId = $employee->userEmployee->first()->id ?? 0;
        $user = User::findOrFail($userId);

        $target = 0;
        $score = 0;

        if ($automated_id === 'C') {
            $target = (double)$this->queryCallTarget($date);
            $score = $target ? $this->getCall($date, $userId) : 0;
        }
        else if ($automated_id === 'EC') {
            $target = (double)$this->queryEffectiveCallTarget($date);
            $score = $target ? $this->getEffectiveCall($date, $userId) : 0;
        }
        else if ($automated_id === 'V') {
            $target = (double)$this->queryValueTarget($date);
            $score = $target ? $this->getValue($date, $userId) : 0;
        }

        return ['score' => $score, 'target' => $target];
    }

    private function getCall($date, $userId)
    {
        $query = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->select(DB::raw('count(forms.id) as total'))
            ->whereBetween('forms.date', [
                date('Y-m-d 00:00:00', strtotime($date)),
                date('Y-m-d 23:59:59', strtotime($date))
            ])
            ->where('forms.created_by', $userId)
            ->groupBy('forms.created_by')
            ->first();

        return $query ? $query->total : 0;
    }

    private function getEffectiveCall($date, $userId)
    {
        $querySalesVisitationHasDetail = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('pin_point_sales_visitation_details', 'pin_point_sales_visitation_details.sales_visitation_id', '=', 'pin_point_sales_visitations.id')
            ->select('pin_point_sales_visitations.id')
            ->addSelect(DB::raw('sum(pin_point_sales_visitation_details.quantity) as totalQty'))
            ->whereBetween('forms.date', [
                date('Y-m-d 00:00:00', strtotime($date)),
                date('Y-m-d 23:59:59', strtotime($date))
            ])
            ->groupBy('pin_point_sales_visitations.id');

        $query = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->joinSub($querySalesVisitationHasDetail, 'query_sales_visitation_has_detail', function ($join) {
                $join->on('pin_point_sales_visitations.id', '=', 'query_sales_visitation_has_detail.id');
            })
            ->selectRaw('count(pin_point_sales_visitations.id) as total')
            ->whereBetween('forms.date', [
                date('Y-m-d 00:00:00', strtotime($date)),
                date('Y-m-d 23:59:59', strtotime($date))
            ])
            ->where('forms.created_by', $userId)
            ->groupBy('forms.created_by')
            ->first();

        return $query ? $query->total : 0;
    }

    private function getValue($date, $userId)
    {
        $query = SalesVisitation::join('forms', 'forms.id','=',SalesVisitation::getTableName().'.form_id')
            ->join(SalesVisitationDetail::getTableName(), SalesVisitationDetail::getTableName().'.sales_visitation_id', '=', SalesVisitation::getTableName().'.id')
            ->groupBy('forms.created_by')
            ->selectRaw('sum(quantity * price) as value')
            ->whereBetween('forms.date', [
                date('Y-m-d 00:00:00', strtotime($date)),
                date('Y-m-d 23:59:59', strtotime($date))
            ])
            ->where('forms.created_by', $userId)
            ->first();

        return $query ? $query->value : 0;
    }

    private function queryCallTarget($date)
    {
        $query = SalesVisitationTarget::whereBetween('date', [
            date('Y-m-d 00:00:00', strtotime($date)),
            date('Y-m-d 23:59:59', strtotime($date))
        ])->first();

        return $query ? $query->call : 0;
    }

    private function queryEffectiveCallTarget($date)
    {
        $query = SalesVisitationTarget::whereBetween('date', [
            date('Y-m-d 00:00:00', strtotime($date)),
            date('Y-m-d 23:59:59', strtotime($date))
        ])->first();
        
        return $query ? $query->effective_call : 0;
    }

    private function queryValueTarget($date)
    {
        $query = SalesVisitationTarget::whereBetween('date', [
            date('Y-m-d 00:00:00', strtotime($date)),
            date('Y-m-d 23:59:59', strtotime($date))
        ])->first();
        
        return $query ? $query->value : 0;
    }
}
