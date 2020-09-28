<?php

namespace App\Http\Controllers\Api\Plugin\SalaryNonSales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\SalaryNonSales\JobValueGroupRequest;
use App\Model\Plugin\SalaryNonSales\Group;
use Illuminate\Http\Request;
use App\Http\Resources\ApiCollection;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups =  Group::with('factors.criterias')->get();

        return new ApiCollection($groups);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\JobValueGroupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(JobValueGroupRequest $request)
    {
        $group = Group::create(['name' => $request->name]);

        return response()->json([
            'message' => 'successfully created',
            'data' => $group,
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
     * @param  \Illuminate\Http\JobValueGroupRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(JobValueGroupRequest $request, Group $group)
    {
        $group->fill($request->all());
        $group->save();

        return response()->json([
            'message' => 'successfully edited',
            'data' => $group,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        if ($group->factors()->exists()) {
            foreach ($group->factors as $factor) {
                $factor->criterias()->delete();
            }

            $group->factors()->delete();
        }

        $group->delete();

        return response()->json([
            'message' => 'successfully deleted',
            'data' => $group,
        ]);
    }
}
