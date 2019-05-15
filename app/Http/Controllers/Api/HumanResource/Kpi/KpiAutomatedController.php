<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\HumanResource\Kpi\Automated;

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

        if (count($request->automated_ids) > 0 && $request->startDate && $request->endDate) {
            foreach ($request->automated_ids as $automated_id) {
                $returnable[$automated_id] = Automated::getData($automated_id, $request->startDate, $request->endDate, $request->employeeId);
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
}
