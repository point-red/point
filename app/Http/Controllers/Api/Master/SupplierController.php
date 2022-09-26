<?php

namespace App\Http\Controllers\Api\Master;

use App\Exports\Master\SupplierExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\Supplier\StoreSupplierRequest;
use App\Http\Requests\Master\Supplier\UpdateSupplierRequest;
use App\Http\Requests\Master\Supplier\ImportSupplierRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Imports\Master\SupplierImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Model\Master\Address;
use App\Model\Master\Bank;
use App\Model\Master\ContactPerson;
use App\Model\Master\Email;
use App\Model\Master\Phone;
use App\Model\Master\Supplier;
use App\Model\Master\SupplierGroup;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \App\Http\Resources\ApiCollection
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::from(Supplier::getTableName().' as '.Supplier::$alias)->eloquentFilter($request);

        $suppliers = Supplier::joins($suppliers, $request->get('join'));

        if ($request->get('group_id')) {
            $suppliers = $suppliers->join('groupables', function ($q) use ($request) {
                $q->on('groupables.groupable_id', '=', 'suppliers.id')
                    ->where('groupables.groupable_type', Supplier::$morphName)
                    ->where('groupables.group_id', '=', $request->get('group_id'));
            });
        }

        if ($request->get('is_archived')) {
            $suppliers = $suppliers->whereNotNull('archived_at');
        } else {
            $suppliers = $suppliers->whereNull('archived_at');
        }

        $suppliers = pagination($suppliers, $request->get('limit'));

        return new ApiCollection($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\Master\Supplier\StoreSupplierRequest $request
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function store(StoreSupplierRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        $supplier = new Supplier;
        $supplier->fill($request->all());
        $supplier->save();

        if ($request->has('groups')) {
            foreach ($request->get('groups') as $arrGroups) {
                if (! empty($arrGroups['name'])) {
                    $group = SupplierGroup::where('name', $arrGroups['name'])->first();
                    if (! $group) {
                        $group = new SupplierGroup;
                        $group->name = $arrGroups['name'];
                        $group->save();
                    }
                }
            }
            $groups = Arr::pluck($request->get('groups'), 'id');
            $groups = array_filter($groups, 'strlen');
            $supplier->groups()->sync($groups);
        }

        Address::saveFromRelation($supplier, $request->get('addresses'));
        Phone::saveFromRelation($supplier, $request->get('phones'));
        Email::saveFromRelation($supplier, $request->get('emails'));
        ContactPerson::saveFromRelation($supplier, $request->get('contacts'));
        // Bank::saveFromRelation($supplier, $request->get('banks'));

        DB::connection('tenant')->commit();

        return new ApiResource($supplier);
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param  int $id
     * @return \App\Http\Resources\ApiResource
     */
    public function show(Request $request, $id)
    {
        $supplier = Supplier::from(Supplier::getTableName().' as '.Supplier::$alias)->eloquentFilter($request);

        $supplier = Supplier::joins($supplier, $request->get('join'));

        $supplier = $supplier->where(Supplier::$alias.'.id', $id)->first();

        if ($request->get('total_payable')) {
            $supplier->total_payable = $supplier->totalAccountPayable();
        }
        if ($request->get('total_receivable')) {
            $supplier->total_payable = $supplier->totalAccountPayable();
        }

        return new ApiResource($supplier);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Master\Supplier\UpdateSupplierRequest $request
     * @param $id
     *
     * @return \App\Http\Resources\ApiResource
     */
    public function update(UpdateSupplierRequest $request, $id)
    {
        DB::connection('tenant')->beginTransaction();

        $supplier = Supplier::findOrFail($id);
        $supplier->fill($request->all());
        $supplier->save();

        if ($request->has('groups')) {
            foreach ($request->get('groups') as $arrGroups) {
                if (! empty($arrGroups['name'])) {
                    $group = Supplier::where('name', $arrGroups['name'])->first();
                    if (! $group) {
                        $group = new Supplier;
                        $group->name = $arrGroups['name'];
                        $group->save();
                    }
                }
            }
            $groups = Arr::pluck($request->get('groups'), 'id');
            $groups = array_filter($groups, 'strlen');
            $supplier->groups()->sync($groups);
        }

        Address::saveFromRelation($supplier, $request->get('addresses'));
        Phone::saveFromRelation($supplier, $request->get('phones'));
        Email::saveFromRelation($supplier, $request->get('emails'));
        ContactPerson::saveFromRelation($supplier, $request->get('contacts'));
        Bank::saveFromRelation($supplier, $request->get('banks'));

        DB::connection('tenant')->commit();

        return new ApiResource($supplier);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $supplier = Supplier::findOrFail($id);

        try {
            $supplier->delete();
        } catch (QueryException $e) {
            $supplier->disabled = true;
            $supplier->save();
        }

        return response()->json([], 204);
    }

    public function import(ImportSupplierRequest $request)
    {
        try {
            $result = new SupplierImport;
            Excel::import($result, request()->file('file'));

            return response()->json([
                'message' => 'success',
                'data' => [ 
                    "success" =>$result->getResult(),
                    "errors" => $result->errors(),
                    "failures" => $result->failures()
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ],200);
        }
        
    }

    public function export(Request $request)
    {
        try {
            $tenant = strtolower($request->header('Tenant'));
            $key = Str::random(16);
            $fileName = strtoupper($tenant).' - Supplier';
            $fileExt = 'xlsx';
            $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

            Excel::store(new SupplierExport($tenant, $request), $path, env('STORAGE_DISK'));

            $cloudStorage = new CloudStorage;
            $cloudStorage->file_name = $fileName;
            $cloudStorage->file_ext = $fileExt;
            $cloudStorage->feature = 'Supplier Export';
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
            return response()->json([
                'message' => $th->getMessage(),
            ],200);
        }
    }
}
