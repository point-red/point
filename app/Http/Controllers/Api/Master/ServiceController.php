<?php

namespace App\Http\Controllers\Api\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Service\StoreServiceRequest;
use App\Http\Requests\Master\Service\UpdateServiceRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Master\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $services = Service::eloquentFilter($request);

        $services = $services->paginate($request->get('paginate') ?? 20);

        return new ApiCollection($services);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreServiceRequest $request
     * @return ApiResource
     */
    public function store(StoreServiceRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $service = new Service;
        $service->fill($request->all());
        $service->save();

        DB::connection('tenant')->commit();

        return new ApiResource($service);
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
        $service = Service::eloquentFilter($request)
            ->with('groups')
            ->findOrFail($id);

        return new ApiResource($service);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateServiceRequest $request
     * @param  int $id
     * @return ApiResource
     */
    public function update(UpdateServiceRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $service = Service::findOrFail($id);
        $service->fill($request->all());
        $service->save();

        DB::connection('tenant')->commit();

        return new ApiResource($service);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([], 204);
    }
}
