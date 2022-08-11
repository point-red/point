<?php

namespace App\Http\Controllers\Api\Sales\PaymentCollection;

use App\Http\Controllers\Controller;
use App\Exceptions\BranchNullException;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use App\Http\Requests\Sales\PaymentCollection\PaymentCollection\StorePaymentCollectionRequest;
use App\Http\Requests\Sales\PaymentCollection\PaymentCollection\UpdatePaymentCollectionRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\Sales\PaymentCollection\PaymentCollection;
use App\Model\Sales\PaymentCollection\PaymentCollectionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use App\Exports\Sales\PaymentCollection\PaymentCollectionExport;
use App\Model\CloudStorage;
use App\Model\Form;
use Throwable;
use App\Exceptions\AmountCollectedInvalidException;

class PaymentCollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $paymentCollection = PaymentCollection::from(PaymentCollection::getTableName().' as '.PaymentCollection::$alias)->eloquentFilter($request);

        $paymentCollection = PaymentCollection::joins($paymentCollection, $request->get('join'));

        $paymentCollection = pagination($paymentCollection, $request->get('limit'));
        
        return new ApiCollection($paymentCollection);
    }

    /**
     * Store a newly created resource in storage.
     * Request :
     *  - number (String)
     *  - date (String YYYY-MM-DD hh:mm:ss)
     *  - sales_request_id (Int, Optional)
     *  - sales_contract_id (Int, Optional)
     *  - customer_id (Int)
     *  - warehouse_id (Int, Optional)
     *  - eta (Date)
     *  - cash_only (Boolean, Optional)
     *  - need_down_payment (Decimal, Optional, Default 0)
     *  - delivery_fee (Decimal, Optional)
     *  - discount_percent (Decimal, Optional)
     *  - discount_value (Decimal, Optional)
     *  - type_of_tax (String ['include', 'exclude', 'non'])
     *  - tax (Decimal)
     *  -
     *  - items (Array) :
     *      - item_id (Int)
     *      - quantity (Decimal)
     *      - unit (String)
     *      - converter (Decimal)
     *      - price (Decimal)
     *      - discount_percent (Decimal, Optional)
     *      - discount_value (Decimal, Optional)
     *      - taxable (Boolean, Optional)
     *      - description (String)
     *      - allocation_id (Int, Optional).
     *
     * @param StorePaymentCollectionRequest $request
     * @return ApiResource
     * @throws Throwable
     */
    public function store(StorePaymentCollectionRequest $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $paymentCollection = PaymentCollection::create($request->all());
            $paymentCollection
                ->load('form');

            return new ApiResource($paymentCollection);
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
        $paymentCollection = PaymentCollection::from(PaymentCollection::getTableName().' as '.PaymentCollection::$alias)->eloquentFilter($request);

        $paymentCollection = PaymentCollection::joins($paymentCollection, $request->get('join'));

        $paymentCollection = $paymentCollection->where(PaymentCollection::$alias.'.id', $id)->first();

        return new ApiResource($paymentCollection);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdatePaymentCollectionRequest $request
     * @param int $id
     * @return ApiResource
     * @throws Throwable
     */
    public function update(UpdatePaymentCollectionRequest $request, $id)
    {
        $paymentCollection = PaymentCollection::findOrFail($id);
        $paymentCollection->isAllowedToUpdate();
        
        $branches = tenant(auth()->user()->id)->branches;
        $userBranch = null;
        foreach ($branches as $branch) {
            if ($branch->pivot->is_default) {
                $userBranch = $branch->id;
                break;
            }
        }

        if ($paymentCollection->form->branch_id != $userBranch) {
            throw new BranchNullException();
        }

        $result = DB::connection('tenant')->transaction(function () use ($request, $paymentCollection) {
            $paymentCollection->form->archive();
            
            $request['number'] = $paymentCollection->form->edited_number;
            $request['old_increment'] = $paymentCollection->form->increment;

            $paymentCollection = PaymentCollection::create($request->all());
            
            $paymentCollection
                ->load('form');

            return new ApiResource($paymentCollection);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param  int $id
     * @return Response
     * @throws Throwable
     */
    public function destroy(Request $request, $id)
    {
        $paymentCollection = PaymentCollection::findOrFail($id);
        $paymentCollection->isAllowedToDelete();

        $branches = tenant(auth()->user()->id)->branches;
        $userBranch = null;
        foreach ($branches as $branch) {
            if ($branch->pivot->is_default) {
                $userBranch = $branch->id;
                break;
            }
        }
        
        if ($paymentCollection->form->branch_id != $userBranch) {
            throw new BranchNullException();
        }
        

        $paymentCollection->requestCancel($request);

        return response()->json([], 204);
    }

    public function generateFormNumber(Request $request) {
        $data = $request->all();
        $paymentCollection = new PaymentCollection;
        $paymentCollection->fill($data);

        $defaultNumberPostfix = '{y}{m}{increment=4}';
        $form = new Form;
        $form->fill($data);
        $form->formable_type = $paymentCollection::$morphName;
        $form->generateFormNumber(
            $data['number'] ?? $paymentCollection->defaultNumberPrefix.$defaultNumberPostfix,
            $paymentCollection->customer_id,
            $paymentCollection->supplier_id
        );
        return new ApiResource($form);
    }

    public function export(Request $request)
    {
        $request->validate([
            'data' => 'required',
        ]);
        
        $tenant = strtolower($request->header('Tenant'));

        $dateForm = date('d F Y', strtotime($request->data['date_start']));
        $dateTo = date('d F Y', strtotime($request->data['date_end']));
        
        $key = Str::random(16);
        $fileName = 'Payment Collection_'.$dateForm.'-'.$dateTo;
        $fileExt = 'xlsx';
        $path = 'tmp/'.$tenant.'/'.$key.'.'.$fileExt;
        
        Excel::store(new PaymentCollectionExport($request->data['date_start'], $request->data['date_end'], $request->data['ids'], $request->data['tenant_name']), $path, env('STORAGE_DISK'));
        
        $cloudStorage = new CloudStorage();
        $cloudStorage->file_name = $fileName;
        $cloudStorage->file_ext = $fileExt;
        $cloudStorage->feature = 'payment collection';
        $cloudStorage->key = $key;
        $cloudStorage->path = $path;
        $cloudStorage->disk = env('STORAGE_DISK');
        $cloudStorage->owner_id = auth()->user()->id;
        $cloudStorage->download_url = env('API_URL').'/download?key='.$key;
        $cloudStorage->save();

        return response()->json([
            'data' => [
                'url' => $cloudStorage->download_url,
            ],
        ], 200);
    }
}
