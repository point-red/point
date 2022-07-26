<?php

namespace App\Http\Controllers\Api\Sales\DeliveryNote;

use App\Exports\Sales\DeliveryNote\DeliveryNoteExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\DeliveryNote\DeliveryNote\StoreDeliveryNoteRequest;
use App\Http\Requests\Sales\DeliveryNote\DeliveryNote\UpdateDeliveryNoteRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use App\Model\Sales\DeliveryNote\DeliveryNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryNotes = DeliveryNote::from(DeliveryNote::getTableName().' as '.DeliveryNote::$alias)->eloquentFilter($request);

        $deliveryNotes = DeliveryNote::joins($deliveryNotes, $request->get('join'));

        $deliveryNotes = pagination($deliveryNotes, $request->get('limit'));

        return new ApiCollection($deliveryNotes);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreDeliveryNoteRequest  $request
     * @return Response
     *
     * @throws Throwable
     */
    public function store(StoreDeliveryNoteRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $deliveryNote = DeliveryNote::create($request->all());
            $deliveryNote
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($deliveryNote);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($deliveryNote);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateDeliveryNoteRequest  $request
     * @param  int  $id
     * @return ApiResource
     *
     * @throws Throwable
     */
    public function update(UpdateDeliveryNoteRequest $request, $id)
    {
        $deliveryNote = DeliveryNote::with('form')->findOrFail($id);

        $deliveryNote->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryNote) {
            $deliveryNote->form->archive();
            $request['number'] = $deliveryNote->form->edited_number;
            $request['old_increment'] = $deliveryNote->form->increment;

            $deliveryNote = DeliveryNote::create($request->all());
            $deliveryNote->load(['form', 'customer', 'items']);

            return new ApiResource($deliveryNote);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        $deliveryNote->isAllowedToDelete();

        $response = $deliveryNote->requestCancel($request);

        if (! $response) {
            $deliveryNote->deliveryOrder->form->done = false;
            $deliveryNote->deliveryOrder->form->save();
        }

        return response()->json([], 204);
    }

    /**
     * export.
     *
     * @param  Request  $request
     * @return Response
     */
    public function export(Request $request)
    {
        try {
            $tenant = strtolower($request->header('Tenant'));
            $key = Str::random(16);
            $fileName = strtoupper($tenant).' - Sales Delivery Note';
            $fileExt = 'xlsx';
            $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

            Excel::store(new DeliveryNoteExport($tenant, $request), $path, env('STORAGE_DISK'));

            $cloudStorage = new CloudStorage();
            $cloudStorage->file_name = $fileName;
            $cloudStorage->file_ext = $fileExt;
            $cloudStorage->feature = 'Sales Delivery Note Export';
            $cloudStorage->key = $key;
            $cloudStorage->path = $path;
            $cloudStorage->disk = env('STORAGE_DISK');
            $cloudStorage->project_id = Project::where('code', strtolower($tenant))->first()->id;
            $cloudStorage->owner_id = auth()->user()->id;
            $cloudStorage->expired_at = Carbon::now()->addDay(1);
            $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
            $cloudStorage->save();

            return response()->json([
                'data' => ['url' => env('API_URL').'/download?key='.$key],
            ], 200);
        } catch (\Throwable $th) {
            return response_error($th);
        }
    }
}
