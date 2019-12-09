<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manufacture\ManufactureProcess\StoreManufactureProcessRequest;
use App\Http\Requests\Manufacture\ManufactureProcess\UpdateManufactureProcessRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Manufacture\ManufactureProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $processes = ManufactureProcess::eloquentFilter($request);

        $processes = pagination($processes, $request->get('limit'));

        return new ApiCollection($processes);;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreManufactureProcessRequest $request
     * @return \App\Http\Resources\ApiResource
     * @throws \Throwable
     */
    public function store(StoreManufactureProcessRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $process = new ManufactureProcess;
            $process->fill($request->all());
            $process->save();

            return new ApiResource($process);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $process = ManufactureProcess::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($process);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateManufactureProcessRequest $request
     * @param $id
     * @return ApiResource
     */
    public function update(UpdateManufactureProcessRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $process = ManufactureProcess::findOrFail($id);
            $process->fill($request->all());
            $process->save();

            return new ApiResource($process);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $process = ManufactureProcess::findOrFail($id);
        $process->delete();

        return response()->json([], 204);
    }
}
