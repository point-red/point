<?php

namespace App\Http\Controllers\Api\HumanResource\Employee;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\HumanResource\Employee\EmployeeMaritalStatus;
use App\Http\Resources\HumanResource\Employee\EmployeeMaritalStatus\EmployeeMaritalStatusCollection;

class EmployeeMaritalStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new EmployeeMaritalStatusCollection(EmployeeMaritalStatus::all());
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
