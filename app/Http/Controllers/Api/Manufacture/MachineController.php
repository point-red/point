<?php

namespace App\Http\Controllers\Api\Manufacture;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manufacture\ManufactureMachine\StoreManufactureMachineRequest;
use App\Http\Requests\Manufacture\ManufactureMachine\UpdateManufactureMachineRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Manufacture\ManufactureMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MachineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $machines = ManufactureMachine::eloquentFilter($request);

        $machines = pagination($machines, $request->get('limit'));

        $id = DB::table('INFORMATION_SCHEMA.TABLES')
            ->select('AUTO_INCREMENT as id')
            ->where('TABLE_SCHEMA', env('DB_DATABASE', 'point').'_'.$request->header('Tenant'))
            ->where('TABLE_NAME', 'manufacture_machines')
            ->first();

        return (new ApiCollection($machines))
            ->additional([
                'next_id' => $id->id
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreManufactureMachineRequest $request
     * @return \App\Http\Resources\ApiResource
     * @throws \Throwable
     */
    public function store(StoreManufactureMachineRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $machine = new ManufactureMachine;
            $machine->fill($request->all());
            $machine->save();

            return new ApiResource($machine);
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
        $machine = ManufactureMachine::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($machine);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateManufactureMachineRequest $request
     * @param $id
     * @return ApiResource
     */
    public function update(UpdateManufactureMachineRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $machine = ManufactureMachine::findOrFail($id);
            $machine->fill($request->all());
            $machine->save();

            return new ApiResource($machine);
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
        $machine = ManufactureMachine::findOrFail($id);
        $machine->delete();

        return response()->json([], 204);
    }
}
