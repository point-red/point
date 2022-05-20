<?php

namespace App\Http\Controllers\Api\Sales\DeliveryOrder;

use Throwable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Sales\DeliveryOrder\DeliveryOrderExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\DeliveryOrder\DeliveryOrder\StoreDeliveryOrderRequest;
use App\Http\Requests\Sales\DeliveryOrder\DeliveryOrder\UpdateDeliveryOrderRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\CloudStorage;
use App\Model\Project\Project;
use App\Model\Sales\DeliveryOrder\DeliveryOrder;

class DeliveryOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $deliveryOrders = DeliveryOrder::from(DeliveryOrder::getTableName().' as '.DeliveryOrder::$alias)->eloquentFilter($request);

        $deliveryOrders = DeliveryOrder::joins($deliveryOrders, $request->get('join'));

        $deliveryOrders = pagination($deliveryOrders, $request->get('limit'));

        return new ApiCollection($deliveryOrders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDeliveryOrderRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StoreDeliveryOrderRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $deliveryOrder = DeliveryOrder::create($request->all());
            $deliveryOrder
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation');

            return new ApiResource($deliveryOrder);
        });

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $deliveryOrder = DeliveryOrder::eloquentFilter($request)->findOrFail($id);

        if ($request->has('with_archives')) {
            $deliveryOrder->archives = $deliveryOrder->archives();
        }

        if ($request->get('remaining_info')) {
            $deliveryNotes = $deliveryOrder->deliveryNotes()->with('items')->get();

            foreach ($deliveryOrder->items as $deliveryOrderItem) {
                $deliveryOrderItem->quantity_pending = $deliveryOrderItem->quantity;

                foreach ($deliveryNotes as $deliveryNote) {
                    $deliveryNoteItem = $deliveryNote->items->firstWhere('delivery_order_item_id', $deliveryOrderItem->id);
                    if ($deliveryNoteItem) {
                        $deliveryOrderItem->quantity_pending -= $deliveryNoteItem->quantity;
                    }
                }
            }
        }

        return new ApiResource($deliveryOrder);
    }

    public function showReceipt(Request $request, $id)
    {
        $tenantCode = strtolower($request->header('Tenant'));
        $tenant = Project::where('code', $tenantCode)->first();

        $deliveryOrder = DeliveryOrder::eloquentFilter($request)->findOrFail($id);

        return view('emails.sales.delivery-order.delivery-order-receipt', compact('deliveryOrder', 'tenant'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDeliveryOrderRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdateDeliveryOrderRequest $request, $id)
    {
        // TODO prevent delete if referenced by delivery order
        $deliveryOrder = DeliveryOrder::with('form')->findOrFail($id);

        $deliveryOrder->isAllowedToUpdate();

        $result = DB::connection('tenant')->transaction(function () use ($request, $deliveryOrder) {
            $deliveryOrder->form->archive($request->notes);
            $request['number'] = $deliveryOrder->form->edited_number;
            $request['old_increment'] = $deliveryOrder->form->increment;

            $deliveryOrder = DeliveryOrder::create($request->all());
            $deliveryOrder->load(['form', 'customer', 'items']);

            return new ApiResource($deliveryOrder);
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
        try {
            $deliveryOrder = DeliveryOrder::findOrFail($id);
            $deliveryOrder->isAllowedToDelete();
    
            $request->validate([ 'reason' => 'required']);
            
            $response = $deliveryOrder->requestCancel($request);
        } catch (\Throwable $th) {
            return response()->json(['code' => $th->getCode(), 'message' => $th->getMessage()], 422);
        }

        return response()->json([], 204);
    }

    public function export(Request $request)
    {
        try {
            $tenant = strtolower($request->header('Tenant'));
            $key = Str::random(16);
            $fileName = strtoupper($tenant).' - Sales Delivery Order';
            $fileExt = 'xlsx';
            $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;

            Excel::store(new DeliveryOrderExport($tenant, $request), $path, env('STORAGE_DISK'));

            $cloudStorage = new CloudStorage;
            $cloudStorage->file_name = $fileName;
            $cloudStorage->file_ext = $fileExt;
            $cloudStorage->feature = 'Sales Delivery Order';
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
                'code' => $th->getCode(),
                'message' => 'Failed to export, '.$th->getMessage(),
            ], 500);
        }
    }
}
