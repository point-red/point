<?php

namespace App\Http\Controllers\Api\Plugin\PinPoint;

use App\Http\Requests\Plugin\PinPoint\SalesVisitation\StoreSalesVisitationRequest;
use App\Http\Resources\ApiResource;
use App\Http\Resources\Plugin\PinPoint\SalesVisitation\SalesVisitationCollection;
use App\Model\Form;
use App\Model\Master\Customer;
use App\Model\Master\Item;
use App\Model\Plugin\PinPoint\SalesVisitation;
use App\Model\Plugin\PinPoint\SalesVisitationDetail;
use App\Model\Plugin\PinPoint\SalesVisitationInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationNotInterestReason;
use App\Model\Plugin\PinPoint\SalesVisitationSimilarProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SalesVisitationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return SalesVisitationCollection
     */
    public function index(Request $request)
    {
        $salesVisitationForm = SalesVisitation::join('forms', 'forms.id', '=', 'pin_point_sales_visitations.form_id')
            ->with('form.createdBy')
            ->with('interestReasons')
            ->with('notInterestReasons')
            ->with('similarProducts')
            ->with('details.item')
            ->select('pin_point_sales_visitations.*');

        if ($request->get('customer_id')) {
            $salesVisitationForm = $salesVisitationForm->where('customer_id', $request->get('customer_id'));
        }

        $dateFrom = date('Y-m-d 00:00:00', strtotime($request->get('date_from')));
        $dateTo = date('Y-m-d 23:59:59', strtotime($request->get('date_to')));
        $salesVisitationForm = $salesVisitationForm->whereBetween('forms.date', [$dateFrom, $dateTo]);

        if (!tenant()->hasPermissionTo('read pin point sales visitation form')) {
            $salesVisitationForm = $salesVisitationForm->where('forms.created_by', auth()->user()->id);
        }

        $salesVisitationForm = $salesVisitationForm->get();

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
            return response()->json([],422);
        }

        $customer = Customer::where('name', $request->get('customer'))->first();
        $isNewCustomer = false;

        if (!$customer) {
            $customer = new Customer;
            $customer->name = $request->get('customer');
            $customer->save();

            $isNewCustomer = true;
        }

        $form = new Form;
        $form->date = date('Y-m-d H:i:s', strtotime($request->get('date')));
        $form->save();

        $salesVisitation = new SalesVisitation;
        $salesVisitation->form_id = $form->id;
        $salesVisitation->customer_id = $customer->id;
        $salesVisitation->name = $request->get('customer');
        $salesVisitation->phone = $request->get('phone');
        $salesVisitation->address = $request->get('address');
        $salesVisitation->sub_district = $request->get('sub_district');
        $salesVisitation->district = $request->get('district');
        $salesVisitation->latitude = $request->get('latitude');
        $salesVisitation->longitude = $request->get('longitude');
        $salesVisitation->group = $request->get('group');
        $salesVisitation->payment_method = $request->get('payment_method');
        $salesVisitation->payment_received = $request->get('payment_received');
        $salesVisitation->due_date = $request->get('due_date');
        $salesVisitation->save();

        // Interest Reason
        $arrayInterestReason = explode(',', $request->get('interest_reason'));

        if ($arrayInterestReason) {
            for ($i = 0; $i < count($arrayInterestReason); $i++) {
                $interestReason = new SalesVisitationInterestReason;
                $interestReason->sales_visitation_id = $salesVisitation->id;
                $interestReason->name = $arrayInterestReason[$i];
                $interestReason->save();
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
                $notInterestReason = new SalesVisitationNotInterestReason;
                $notInterestReason->sales_visitation_id = $salesVisitation->id;
                $notInterestReason->name = $arrayNotInterestReason[$i];
                $notInterestReason->save();
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
                $similarProduct = new SalesVisitationSimilarProduct;
                $similarProduct->sales_visitation_id = $salesVisitation->id;
                $similarProduct->name = $arraySimilarProduct[$i];
                $similarProduct->save();
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

        if ($array_item) {
            for ($i = 0; $i < count($array_item); $i++) {
                if ($array_item[$i] && $array_price[$i] && $array_quantity[$i]) {
                    $item = Item::where('name', $array_item[$i])->first();
                    if (!$item) {
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
                }
            }

            if (count($array_item) > 0 && $isNewCustomer == false) {
                $salesVisitation->is_repeat_order = true;
                $salesVisitation->save();
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
