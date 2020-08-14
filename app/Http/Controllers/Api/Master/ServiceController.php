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
        $services = Service::from(Service::getTableName().' as '.Service::$alias)->eloquentFilter($request);

        $services = Service::joins($services, $request->get('join'));

        if ($request->get('is_archived')) {
            $services = $services->whereNotNull('archived_at');
        } else {
            $services = $services->whereNull('archived_at');
        }

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
        $service = Service::from(Service::getTableName().' as '.Service::$alias)->eloquentFilter($request);

        $service = Service::joins($service, $request->get('join'));

        $service = $service->where(Service::$alias.'.id', $id)->first();

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
