<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Requests\HumanResource\Kpi\KpiGroup\StoreKpiGroupRequest;
use App\Http\Requests\HumanResource\Kpi\KpiGroup\UpdateKpiGroupRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupCollection;
use App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupResource;
use App\Model\HumanResource\Kpi\KpiGroup;
use Illuminate\Http\Request;

class KpiGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiGroupCollection(KpiGroup::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiGroup\StoreKpiGroupRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupResource
     */
    public function store(StoreKpiGroupRequest $request)
    {
        $kpiGroup = new KpiGroup();
        $kpiGroup->kpi_category_id = $request->input('kpi_category_id');
        $kpiGroup->name = $request->input('name');
        $kpiGroup->save();

        return new KpiGroupResource($kpiGroup);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupResource
     */
    public function show($id)
    {
        return new KpiGroupResource(KpiGroup::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiGroup\UpdateKpiGroupRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiGroup\KpiGroupResource
     */
    public function update(UpdateKpiGroupRequest $request, $id)
    {
        $kpiGroup = KpiGroup::findOrFail($id);
        $kpiGroup->kpi_category_id = $request->input('kpi_category_id');
        $kpiGroup->name = $request->input('name');
        $kpiGroup->save();

        return new KpiGroupResource($kpiGroup);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        KpiGroup::findOrFail($id)->delete();

        return response(null, 204);
    }
}
