<?php

namespace App\Http\Controllers\Api\Plugin\SalaryNonSales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Plugin\SalaryNonSales\JobValueGroupFactorRequest;
use App\Models\Plugin\SalaryNonSales\GroupFactor;
use App\Http\Resources\ApiCollection;

class GroupFactorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $query = new GroupFactor;

        if($groupId = $request->group_id) {
            $query = $query->where('group_id', $groupId);
        }
        
        $group_factors = \pagination($query, $request->limit ?: 10);

        return new ApiCollection($group_factors);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\JobValueGroupFactorRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JobValueGroupFactorRequest $request)
    {
        $factor = GroupFactor::create($request->all());

        return response()->json([
            'message' => 'created',
            'data' => $factor
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
     * @param  \Illuminate\Http\JobValueGroupFactorRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JobValueGroupFactorRequest $request, GroupFactor $group_factor)
    {
        $group_factor->fill($request->all());
        $group_factor->save();

        return response()->json([
            'message' => 'updated', 
            'data' => $group_factor
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(GroupFactor $group_factor)
    {
        $group_factor->criterias()->delete();
        $group_factor->delete();

        return response()->json([
            'message' => 'deleted',
            'data' => $group_factor
        ]);   
    }
}
