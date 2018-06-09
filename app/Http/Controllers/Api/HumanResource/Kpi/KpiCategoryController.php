<?php

namespace App\Http\Controllers\Api\HumanResource\Kpi;

use App\Http\Requests\HumanResource\Kpi\KpiCategory\StoreKpiCategoryRequest;
use App\Http\Requests\HumanResource\Kpi\KpiCategory\UpdateKpiCategoryRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCategoryCollection;
use App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCategoryResource;
use App\Model\HumanResource\Kpi\KpiCategory;
use Illuminate\Http\Request;

class KpiCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCategoryCollection
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit') ?? 0;

        return new KpiCategoryCollection(KpiCategory::paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiCategory\StoreKpiCategoryRequest $request
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCategoryResource
     */
    public function store(StoreKpiCategoryRequest $request)
    {
        $kpiCategory = new KpiCategory();
        $kpiCategory->name = $request->input('name');
        $kpiCategory->date = $request->input('date');
        $kpiCategory->person_id = $request->input('person_id');
        $kpiCategory->save();

        return new KpiCategoryResource($kpiCategory);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCategoryResource
     */
    public function show($id)
    {
        return new KpiCategoryResource(KpiCategory::findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\HumanResource\Kpi\KpiCategory\UpdateKpiCategoryRequest $request
     * @param  int                                                                      $id
     *
     * @return \App\Http\Resources\HumanResource\Kpi\KpiCategory\KpiCategoryResource
     */
    public function update(UpdateKpiCategoryRequest $request, $id)
    {
        $kpiCategory = KpiCategory::findOrFail($id);
        $kpiCategory->name = $request->input('name');
        $kpiCategory->date = $request->input('date');
        $kpiCategory->person_id = $request->input('person_id');
        $kpiCategory->save();

        return new KpiCategoryResource($kpiCategory);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        KpiCategory::findOrFail($id)->delete();

        return response(null, 204);
    }
}
