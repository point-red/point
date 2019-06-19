<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Model\Form;
use App\Model\Master\Group;
use App\Model\Master\Item;
use Illuminate\Http\Request;
use App\Model\Master\Customer;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;
use App\Model\Plugin\PinPoint\SalesVisitationNotInterestReason;
use App\Http\Resources\Plugin\PinPoint\SalesVisitation\SalesVisitationCollection;
use App\Http\Requests\Plugin\PinPoint\SalesVisitation\StoreSalesVisitationRequest;

class SalesVisitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return SalesVisitationCollection
     * @throws \Exception
     */
    public function index(Request $request)
    {
        $salesVisitationForm = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->join('customers', 'customers.id', '=', 'pin_point_sales_visitations.customer_id')
            ->join('users', 'users.id', '=', 'forms.created_by')
            ->with('form.createdBy')
            ->with('interestReasons')
            ->with('notInterestReasons')
            ->with('similarProducts')
            ->with('details.item')
            ->eloquentFilter($request);

        if ($request->get('customer_id')) {
            $salesVisitationForm = $salesVisitationForm->where('customer_id', $request->get('customer_id'));
        }

        $dateFrom = date_from($request->get('date_from'), false, true);
        $dateTo = date_to($request->get('date_to'), false, true);

        $salesVisitationForm = $salesVisitationForm->whereBetween('forms.date', [$dateFrom, $dateTo]);

        if (! tenant()->hasPermissionTo('read pin point sales visitation form')) {
            $salesVisitationForm = $salesVisitationForm->where('forms.created_by', auth()->user()->id);
        }

        $salesVisitationForm = pagination($salesVisitationForm, $request->get('limit'));

        return new SalesVisitationCollection($salesVisitationForm);
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

        if ($request->get('interest_reason') == '' && $request->get('not_interest_reason') == '') {
            return response()->json([], 422);
        }

        if ($request->get('group_id')) {
            $group = Group::findOrFail($request->get('group_id'));
        } else {
            $group = Group::firstOrCreate([
                'name' => $request->get('group')
            ]);
        }

        if ($request->get('customer_id')) {
            $customer = Customer::findOrFail($request->get('customer_id'));
        } else {
            $customer = Customer::firstOrCreate([
                'name' => $request->get('customer_name')
            ]);
        }

        $customer->groups()->attach($group);

        $form = new Form;
        $form->date = date('Y-m-d H:i:s', strtotime($request->get('date')));
        $form->save();

        $salesVisitation = new SalesVisitation;
        $salesVisitation->form_id = $form->id;
        $salesVisitation->customer_id = $customer->id;
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
        $arrayInterestReason = explode(',', $request->get('interest_reason'));

        if ($arrayInterestReason) {
            for ($i = 0; $i < count($arrayInterestReason); $i++) {
                if ($arrayInterestReason[$i]) {
                    $interestReason = new SalesVisitationInterestReason;
                    $interestReason->sales_visitation_id = $salesVisitation->id;
                    $interestReason->name = $arrayInterestReason[$i];
                    $interestReason->save();
                }
            }
        }

        if ($request->get('other_interest_reason')) {
            $interestReason = new SalesVisitationInterestReason;
            $interestReason->sales_visitation_id = $salesVisitation->id;
            $interestReason->name = $request->get('other_interest_reason');
            $interestReason->save();
        }

        // Not Interest Reason
        $arrayNotInterestReason = explode(',', $request->get('not_interest_reason'));

        if ($arrayNotInterestReason) {
            for ($i = 0; $i < count($arrayNotInterestReason); $i++) {
                if ($arrayNotInterestReason[$i]) {
                    $notInterestReason = new SalesVisitationNotInterestReason;
                    $notInterestReason->sales_visitation_id = $salesVisitation->id;
                    $notInterestReason->name = $arrayNotInterestReason[$i];
                    $notInterestReason->save();
                }
            }
        }

        if ($request->get('not_other_interest_reason')) {
            $notInterestReason = new SalesVisitationNotInterestReason;
            $notInterestReason->sales_visitation_id = $salesVisitation->id;
            $notInterestReason->name = $request->get('not_other_interest_reason');
            $notInterestReason->save();
        }

        // Similar Product
        $arraySimilarProduct = explode(',', $request->get('similar_product'));

        if ($arraySimilarProduct) {
            for ($i = 0; $i < count($arraySimilarProduct); $i++) {
                if ($arraySimilarProduct[$i]) {
                    $similarProduct = new SalesVisitationSimilarProduct;
                    $similarProduct->sales_visitation_id = $salesVisitation->id;
                    $similarProduct->name = $arraySimilarProduct[$i];
                    $similarProduct->save();
                }
            }
        }

        if ($request->get('other_similar_product')) {
            $similarProduct = new SalesVisitationSimilarProduct;
            $similarProduct->sales_visitation_id = $salesVisitation->id;
            $similarProduct->name = $request->get('other_similar_product');
            $similarProduct->save();
        }

        // Details
        $array_item = $request->get('item');
        $array_price = $request->get('price');
        $array_quantity = $request->get('quantity');

        $totalVisitation = SalesVisitation::rightJoin(SalesVisitationDetail::getTableName(),
            SalesVisitationDetail::getTableName('sales_visitation_id'), '=', SalesVisitation::getTableName('id'))
            ->where(SalesVisitation::getTableName('name'), $customer->name)->get()->count();

        if ($array_item) {
            for ($i = 0; $i < count($array_item); $i++) {
                if ($array_item[$i] && $array_price[$i] && $array_quantity[$i]) {
                    $item = Item::where('name', $array_item[$i])->first();
                    if (! $item) {
                        $item = new Item;
                        $item->name = $array_item[$i];
                        $item->save();
                    }
                    $detail = new SalesVisitationDetail;
                    $detail->sales_visitation_id = $salesVisitation->id;
                    $detail->item_id = $item->id;
                    $detail->price = $array_price[$i];
                    $detail->quantity = $array_quantity[$i];
                    $detail->save();

                    if ($i == 0 && $totalVisitation > 0) {
                        $salesVisitation->is_repeat_order = true;
                        $salesVisitation->save();
                    }
                }
            }
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
