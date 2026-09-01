<?php

namespace App\Http\Controllers\Api\Inventory\InventoryUsage;

use App\Exports\Inventory\InventoryUsageExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\Usage\StoreRequest;
use App\Http\Requests\Inventory\Usage\UpdateRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\CloudStorage;
use App\Model\Inventory\InventoryUsage\InventoryUsage;
use App\Model\Project\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class InventoryUsageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $inventoryUsages = InventoryUsage::from(InventoryUsage::getTableName().' as '.InventoryUsage::$alias)->eloquentFilter($request);

        $inventoryUsages = InventoryUsage::joins($inventoryUsages, $request->get('join'));

        $inventoryUsages = pagination($inventoryUsages, $request->get('limit'));

        return new ApiCollection($inventoryUsages);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return mixed
     * @throws \Throwable
     */
    public function store(StoreRequest $request)
    {
        try {
            $result = DB::connection('tenant')->transaction(function () use ($request) {
                $inventoryUsage = InventoryUsage::create($request->all());
                $inventoryUsage
                    ->load('form')
                    ->load('items.item')
                    ->load('items.allocation');
        
                return new ApiResource($inventoryUsage);
            });
        } catch (\Throwable $th) {
            return response_error($th);
        }
        
        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $inventoryUsage = InventoryUsage::eloquentFilter($request)->with('form.createdBy')->findOrFail($id);

        if ($request->has('with_archives')) {
            $inventoryUsage->archives = $inventoryUsage->archives();
        }

        if ($request->has('with_origin')) {
            $inventoryUsage->origin = $inventoryUsage->origin();
        }

        return new ApiResource($inventoryUsage);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param $id
     * @return ApiResource
     * @throws \Throwable
     */
    public function update(UpdateRequest $request, $id)
    {
        $inventoryUsage = InventoryUsage::with('form')->findOrFail($id);

        $inventoryUsage->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $inventoryUsage) {
            $inventoryUsage->form->archive();
            $request['number'] = $inventoryUsage->form->edited_number;
            $request['old_increment'] = $inventoryUsage->form->increment;

            $inventoryUsage = InventoryUsage::create($request->all());
            $inventoryUsage
                ->load('form')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($inventoryUsage);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $inventoryUsage = InventoryUsage::findOrFail($id);

        $inventoryUsage->isAllowedToDelete();

        $inventoryUsage->requestCancel($request);

        return response()->json([], 204);
    }

    public function export(Request $request)
    {
        try {
            $tenant = strtolower($request->header('Tenant'));
            $key = Str::random(16);
            $fileName = strtoupper($tenant).' - Inventory Usage';
            $fileExt = 'xlsx';
            $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

            Excel::store(new InventoryUsageExport($tenant, $request), $path, env('STORAGE_DISK'));

            $cloudStorage = new CloudStorage();
            $cloudStorage->file_name = $fileName;
            $cloudStorage->file_ext = $fileExt;
            $cloudStorage->feature = 'Inventory Usage Export';
            $cloudStorage->key = $key;
            $cloudStorage->path = $path;
            $cloudStorage->disk = env('STORAGE_DISK');
            $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
            $cloudStorage->owner_id = auth()->user()->id;
            $cloudStorage->expired_at = Carbon::now()->addDay(1);
            $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
            $cloudStorage->save();

            return response()->json([
                'data' => [ 'url' => $cloudStorage->download_url ],
            ], 200);
        } catch (\Throwable $th) {
            return response_error($th);
        }
    }
}
