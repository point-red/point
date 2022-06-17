<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Helpers\Inventory\InventoryHelper;
use App\Helpers\Reward\TokenHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plugin\PinPoint\SalesVisitation\StoreSalesVisitationRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Model\CloudStorage;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\CustomerGroup;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationNoInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;
use App\Model\Project\Project;
use App\Wrapper\CarbonWrapper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SalesVisitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return ApiCollection
     * @throws \Exception
     */
    public function index(Request $request)
    {
        if(tenant(auth()->user()->id)->roles[0]->name == 'super admin') {
            $salesVisitationForm = SalesVisitation::from(SalesVisitation::getTableName().' as '.SalesVisitation::$alias)
            ->join(Form::getTableName().' as '.Form::$alias, 'form.id', '=', 'sales_visitation.form_id')
            ->with('form.createdBy')
            ->with('interestReasons')
            ->with('noInterestReasons')
            ->with('similarProducts')
            ->with('details.item')
            ->eloquentFilter($request);
        } else {
            $salesVisitationForm = SalesVisitation::from(SalesVisitation::getTableName().' as '.SalesVisitation::$alias)
            ->join(Form::getTableName().' as '.Form::$alias, 'form.id', '=', 'sales_visitation.form_id')
            ->where('form.created_by', '=', auth()->user()->id)
            ->with('form.createdBy')
            ->with('interestReasons')
            ->with('noInterestReasons')
            ->with('similarProducts')
            ->with('details.item')
            ->eloquentFilter($request);
        }
        
        $salesVisitationForm = SalesVisitation::joins($salesVisitationForm, $request->get('join'));

        if ($request->get('customer_id')) {
            $salesVisitationForm = $salesVisitationForm->where('sales_visitation.customer_id', $request->get('customer_id'));
        }

        $dateFrom = date_from($request->get('date_from'), false, true);
        $dateTo = date_to($request->get('date_to'), false, true);

        $salesVisitationForm = $salesVisitationForm->whereBetween('form.date', [$dateFrom, $dateTo]);

        if (! tenant()->hasPermissionTo('read pin point sales visitation form')) {
            $salesVisitationForm = $salesVisitationForm->where('form.created_by', auth()->user()->id);
        }

        if (! empty($request->get('payment_method'))) {
            $salesVisitationForm->where(SalesVisitation::$alias.'.payment_method', $request->get('payment_method'));
        }

        if (! empty($request->get('branch_id'))) {
            $salesVisitationForm->where(SalesVisitation::$alias.'.branch_id', $request->get('branch_id'));
        }

        if (! empty($request->get('item_id'))) {
            $salesVisitationForm->whereIn(SalesVisitation::$alias.'.id', function ($query) use ($request) {
                $query->select('sales_visitation_id')->from(SalesVisitationDetail::getTableName())->where('item_id', $request->get('item_id'));
            });
        }

        if (! empty($request->get('item_sold'))) {
            if ($request->get('item_sold') == 'item_sold') {
                $salesVisitationForm->whereIn(SalesVisitation::$alias.'.id', function ($query) use ($request) {
                    $query->select('sales_visitation_id')->from(SalesVisitationDetail::getTableName());
                });
            } elseif ($request->get('item_sold') == 'no_item_sold') {
                $salesVisitationForm->whereNotIn(SalesVisitation::$alias.'.id', function ($query) use ($request) {
                    $query->select('sales_visitation_id')->from(SalesVisitationDetail::getTableName());
                });
            }
        }

        $salesVisitationForm = pagination($salesVisitationForm, $request->get('limit'));

        foreach ($salesVisitationForm as $svf) {
            $photo = CloudStorage::where('feature', 'sales visitation form')
                ->where('feature_id', $svf->id)
                ->where('feature', 'sales visitation form')
                ->where('project_id', Project::where('code', strtolower($request->header('Tenant')))->first()->id)
                ->first();
            if ($photo) {
                $base64 = base64_encode(Storage::disk($photo->disk)->get($photo->path));
                $preview = 'data:'.$photo->mime_type.';base64,'.$base64;
                $svf->photo = $preview;
            }
        }

        return new ApiCollection($salesVisitationForm);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreSalesVisitationRequest $request
     * @return ApiResource
     */
    public function store(StoreSalesVisitationRequest $request)
    {
        DB::connection('tenant')->beginTransaction();

        if ($request->get('group_id')) {
            $group = CustomerGroup::findOrFail($request->get('group_id'));
        } else {
            $group = CustomerGroup::firstOrCreate([
                'name' => $request->get('group'),
            ]);
        }

        if ($request->get('customer_id')) {
            $customer = Customer::findOrFail($request->get('customer_id'));
        } else {
            $customer = Customer::firstOrCreate([
                'name' => $request->get('customer_name'),
            ]);
        }

        $latest = SalesVisitation::orderBy('created_at', 'desc')->first();
        if ($request->header('Tenant') === 'kopibara' && $latest) {
            $a = CarbonWrapper::create($latest->created_at);
            $b = Carbon::now();

            $limit = 10;
            $wait = $limit - CarbonWrapper::diffInMinute($a, $b);

            if ($limit > CarbonWrapper::diffInMinute($a, $b)) {
                return response()->json([
                    'code' => 422,
                    'message' => 'form kunjungan sales hanya bisa di isi setiap 10 menit sekali, anda harus menunggu '
                        .$wait.' menit untuk bisa membuat form baru',
                    // TODO: see user language preference to translate each message
                    // 'message' => 'Sales visitation form is applicable every 10 minutes, you need to wait '
                    //    . $wait . ' more minutes before create new form',
                ], 422);
            }
        }

        $customer->groups()->syncWithoutDetaching([$group->id], ['created_at' => Carbon::now()]);

        $form = new Form;
        $form->date = date('Y-m-d H:i:s', strtotime($request->get('date')));
        $form->save();
        $form->number = 'SV-' . $form->id;
        $form->save();

        $salesVisitation = new SalesVisitation;
        $salesVisitation->form_id = $form->id;
        $salesVisitation->customer_id = $customer->id;
        $salesVisitation->branch_id = $request->get('branch_id') ?? null;
        $salesVisitation->warehouse_id = $request->get('warehouse_id') ?? null;
        $salesVisitation->name = $request->get('customer_name');
        $salesVisitation->phone = $request->get('phone');
        $salesVisitation->address = $request->get('address');
        $salesVisitation->sub_district = $request->get('sub_district');
        $salesVisitation->district = $request->get('district');
        $salesVisitation->latitude = $request->get('latitude');
        $salesVisitation->longitude = $request->get('longitude');
        $salesVisitation->group = $request->get('group_name');
        $salesVisitation->notes = $request->get('notes');
        $salesVisitation->payment_method = $request->get('payment_method');
        $salesVisitation->payment_received = $request->get('payment_received');
        $salesVisitation->due_date = $request->get('due_date');
        $salesVisitation->save();

        // Interest Reason
        $interestReasons = $request->get('interest_reasons');
        $countInterestReason = 0;
        for ($i = 0; $i < count($interestReasons); $i++) {
            if ($interestReasons[$i]['id'] && $interestReasons[$i]['name']) {
                $interestReason = new SalesVisitationInterestReason;
                $interestReason->sales_visitation_id = $salesVisitation->id;
                $interestReason->name = $interestReasons[$i]['name'];
                $interestReason->save();
                $countInterestReason++;
            }
        }

        // No Interest Reason
        $noInterestReasons = $request->get('no_interest_reasons');
        $countNoInterestReason = 0;
        for ($i = 0; $i < count($noInterestReasons); $i++) {
            if ($noInterestReasons[$i]['id'] && $noInterestReasons[$i]['name']) {
                $noInterestReason = new SalesVisitationNoInterestReason;
                $noInterestReason->sales_visitation_id = $salesVisitation->id;
                $noInterestReason->name = $noInterestReasons[$i]['name'];
                $noInterestReason->save();
                $countNoInterestReason++;
            }
        }

        if ($countInterestReason + $countNoInterestReason == 0) {
            return response()->json([], 422);
        }

        // Similar Product
        $similarProducts = $request->get('similar_products');
        for ($i = 0; $i < count($similarProducts); $i++) {
            if ($similarProducts[$i]['id'] && $similarProducts[$i]['name']) {
                $similarProduct = new SalesVisitationSimilarProduct;
                $similarProduct->sales_visitation_id = $salesVisitation->id;
                $similarProduct->name = $similarProducts[$i]['name'];
                $similarProduct->save();
            }
        }

        $totalVisitation = SalesVisitation::rightJoin(
            SalesVisitationDetail::getTableName(),
            SalesVisitationDetail::getTableName('sales_visitation_id'),
            '=',
            SalesVisitation::getTableName('id')
        )->where(SalesVisitation::getTableName('name'), $customer->name)->get()->count();

        $items = $request->get('items');
        if ($items) {
            for ($i = 0; $i < count($items); $i++) {
                if ($items[$i]['item_id'] && $items[$i]['quantity'] && $items[$i]['price']) {
                    if (get_if_set($items[$i]['dna']) && $items[$i]['dna']) {
                        foreach ($items[$i]['dna'] as $dna) {
                            if ($dna['quantity'] > 0) {

                                $detail = new SalesVisitationDetail;
                                $detail->sales_visitation_id = $salesVisitation->id;
                                $detail->unit = $items[$i]['unit'];
                                $detail->converter = $items[$i]['converter'];
                                $detail->quantity = $dna['quantity'];
                                $detail->production_number = $dna['production_number'];
                                $detail->expiry_date = $dna['expiry_date'];
                                $detail->item_id = $items[$i]['item_id'];
                                $detail->price = $items[$i]['price'];
                                $detail->save();

                                $options = [];
                                if ($detail->item->require_expiry_date) {
                                    $options['expiry_date'] = $detail->expiry_date;
                                }
                                if ($detail->item->require_production_number) {
                                    $options['production_number'] = $detail->production_number;
                                }
                
                                $options['quantity_reference'] = $detail->quantity;
                                $options['unit_reference'] = $detail->unit;
                                $options['converter_reference'] = $detail->converter;
                                if ($request->header('Tenant') === 'kopibara' || $request->header('Tenant') === 'dev') {
                                    InventoryHelper::decrease($form, $detail->salesVisitation->warehouse, $detail->item, $detail->quantity, $detail->unit, $detail->converter, $options);
                                }
                            }
                            
                        }
                    } else {
                        if ($items[$i]['quantity']) {
                            $detail = new SalesVisitationDetail;
                            $detail->sales_visitation_id = $salesVisitation->id;
                            $detail->unit = $items[$i]['unit'];
                            $detail->converter = $items[$i]['converter'];
                            $detail->quantity = $items[$i]['quantity'];
                            $detail->item_id = $items[$i]['item_id'];
                            $detail->price = $items[$i]['price'];
                            $detail->save();
                        
                            $options = [];
                            if ($detail->item->require_expiry_date) {
                                $options['expiry_date'] = $detail->expiry_date;
                            }
                            if ($detail->item->require_production_number) {
                                $options['production_number'] = $detail->production_number;
                            }
            
                            $options['quantity_reference'] = $detail->quantity;
                            $options['unit_reference'] = $detail->unit;
                            $options['converter_reference'] = $detail->converter;
                            if ($request->header('Tenant') === 'kopibara' || $request->header('Tenant') === 'dev' || $request->header('Tenant') === 'demo1234') {
                                if ($salesVisitation->payment_method == 'cash' || $salesVisitation->payment_method == 'credit') {
                                    InventoryHelper::decrease($form, $detail->salesVisitation->warehouse, $detail->item, $detail->quantity, $detail->unit, $detail->converter, $options);
                                }
                            }
                        }
                    }

                    if ($i == 0 && $totalVisitation > 0) {
                        $salesVisitation->is_repeat_order = true;
                        $salesVisitation->save();
                    }
                }
            }
        }
 

        if ($request->get('image')) {
            \App\Helpers\StorageHelper::uploadFromBase64($request->get('image'), 'sales visitation form', $salesVisitation->id);
        }

        if ($salesVisitation->details->count() > 0) {
            TokenHelper::add('sales visitation effective call');
        } else {
            TokenHelper::add('sales visitation call');
        }

        DB::connection('tenant')->commit();

        return new ApiResource($salesVisitation);
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
