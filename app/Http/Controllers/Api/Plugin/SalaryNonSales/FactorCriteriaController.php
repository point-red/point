<?php

namespace App\Http\Controllers\Api\Plugin\SalaryNonSales;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use Illuminate\Http\Request;
use App\Model\Plugin\SalaryNonSales\FactorCriteria;
use App\Http\Requests\Plugin\SalaryNonSales\JobValueFactorCriteriaRequest;


class FactorCriteriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = new FactorCriteria;

        if($factor_criteriaId = $request->factor_id) {
            $query = $query->where('factor_id', $factor_criteriaId);
        }
        
        $criterias = \pagination($query, $request->limit ?: 10);
        
        return new ApiCollection($criterias);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JobValueFactorCriteriaRequest $request)
    {
        $factor_criteria = FactorCriteria::create($request->all());

        return response()->json([
            'message' => 'created',
            'data' => $factor_criteria
        ]);
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JobValueFactorCriteriaRequest $request, FactorCriteria $factor_criteria)
    {
        $factor_criteria->fill($request->all());
        $factor_criteria->save();

        return response()->json([
            'message' => 'updated',
            'data' => $factor_criteria
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(FactorCriteria $factor_criteria)
    {
        $factor_criteria->delete();

        return response()->json([
            'message' => 'deleted',
            'data' => $factor_criteria
        ]);
    }
}
