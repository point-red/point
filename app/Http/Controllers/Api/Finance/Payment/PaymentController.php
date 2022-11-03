<?php

namespace App\Http\Controllers\Api\Finance\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Payment\Payment\StorePaymentRequest;
use App\Http\Requests\Finance\Payment\Payment\UpdatePaymentRequest;
use App\Http\Resources\ApiCollection;
use App\Http\Resources\ApiResource;
use App\Mail\Finance\Payment\PaymentCancellationApprovalRequest;
use App\Model\Auth\Role;
use App\Model\Finance\Payment\Payment;
use App\Model\Finance\PaymentOrder\PaymentOrder;
use App\Model\Master\User;
use App\Model\Purchase\PurchaseDownPayment\PurchaseDownPayment;
use App\Model\Token;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $payment = Payment::from(Payment::getTableName() . ' as ' . Payment::$alias)->eloquentFilter($request);

        $payment = Payment::joins($payment, $request->get('join'));

        $payment = pagination($payment, $request->get('limit'));

        return new ApiCollection($payment);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePaymentRequest $request
     * @return Response
     * @throws Throwable
     */
    public function store(StorePaymentRequest $request)
    {
        return DB::connection('tenant')->transaction(function () use ($request) {
            $payment = Payment::create($request->all());
            $payment
                ->load('form')
                ->load('paymentable')
                ->load('details.allocation')
                ->load('details.referenceable.form');

            return new ApiResource($payment);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return ApiResource
     */
    public function show(Request $request, $id)
    {
        $payment = Payment::eloquentFilter($request)->findOrFail($id);

        return new ApiResource($payment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return Response
     * @throws Throwable
     */
    public function update(UpdatePaymentRequest $request, $id)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $payment = Payment::findOrFail($id);

            $payment->form->archive();

            foreach ($payment->details as $paymentDetail) {
                if (!$paymentDetail->isDownPayment()) {
                    $reference = $paymentDetail->referenceable;
                    $reference->remaining += $paymentDetail->amount;
                    $reference->save();
                    $reference->updateStatus();
                }
            }

            $payment = Payment::create($request->all());

            $payment
                ->load('form')
                ->load('paymentable')
                ->load('details.referenceable')
                ->load('details.allocation');

            return new ApiResource($payment);
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
        $request->validate([
            'reason' => 'required'
        ]);

        $payment = Payment::findOrFail($id);
        $payment->isAllowedToDelete();

        DB::connection('tenant')->transaction(function () use ($payment, $request) {
            $payment->requestCancel($request);

            // Status form cash out jadi pending
            $payment->form->done = 0;
            $payment->form->save();

            // Kirim notifikasi by program & email
            $superAdminRole = Role::where('name', 'super admin')->first();
            $emailUsers = User::whereHas('roles', function (Builder $query) use ($superAdminRole) {
                $query->where('role_id', '=', $superAdminRole->id);
            })->get();

            foreach ($emailUsers as $recipient) {
                // create token based on request_approval_to
                $token = Token::where('user_id', $recipient->id)->first();

                if (!$token) {
                    $token = new Token([
                        'user_id' => $recipient->id,
                        'token' => md5($recipient->email . '' . now()),
                    ]);
                    $token->save();
                }

                Mail::to([
                    $recipient->email,
                ])->queue(new PaymentCancellationApprovalRequest(
                    $payment,
                    $recipient,
                    $payment->form,
                    $token->token
                ));
            }

            // if (!$response) {
            //     foreach ($payment->details as $paymentDetail) {
            //         if (!$paymentDetail->isDownPayment()) {
            //             $reference = $paymentDetail->referenceable;
            //             $reference->remaining += $payment->amount;
            //             $reference->save();
            //             $reference->form->done = false;
            //             $reference->form->save();
            //         }
            //     }
            // }


        });

        return response()->json([], 204);
    }

    public function getReferences(Request $request)
    {
        // Split request filter for each reference type
        $paymentOrderRequest = new Request();
        $downPaymentRequest = new Request();
        $paymentOrderString = 'paymentorder';
        $downPaymentString = 'downpayment';
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['limit', 'page'])) {
                $paymentOrderRequest->merge([
                    $key => $value
                ]);
                $downPaymentRequest->merge([
                    $key => $value
                ]);
                continue;
            }
            $explodedKey = explode('_', $key);

            switch ($explodedKey[0]) {
                case $paymentOrderString:
                    $keyAttribute = substr($key, strlen($paymentOrderString) + 1); //+1 for _
                    $paymentOrderRequest->merge([
                        $keyAttribute => $value
                    ]);
                    break;

                case $downPaymentString:
                    $keyAttribute = substr($key, strlen($downPaymentString) + 1); //+1 for _
                    $downPaymentRequest->merge([
                        $keyAttribute => $value
                    ]);
                    break;

                default:
                    # code...
                    break;
            }
        }

        $references = new Collection();

        $paymentOrders = PaymentOrder::from(PaymentOrder::getTableName() . ' as ' . PaymentOrder::$alias)->eloquentFilter($paymentOrderRequest);
        $paymentOrders = PaymentOrder::joins($paymentOrders, $paymentOrderRequest->get('join'))->get();
        $references = $references->concat($paymentOrders);

        $downPayments = PurchaseDownPayment::from(PurchaseDownPayment::getTableName() . ' as ' . PurchaseDownPayment::$alias)->eloquentFilter($downPaymentRequest);
        $downPayments = PurchaseDownPayment::joins($downPayments, $downPaymentRequest->get('join'))->get();
        $references = $references->concat($downPayments);

        $references = $references->sortBy('date');
        $paginatedReferences = paginate_collection($references, $request->get('limit'), $request->get('page'));

        return new ApiCollection($paginatedReferences);
    }

    public function getPaymentables(Request $request)
    {
        $paymentables = Payment::from(Payment::getTableName() . ' as ' . Payment::$alias)
            ->select(['paymentable_id', 'paymentable_type', 'paymentable_name'])
            ->eloquentFilter($request);
        $paymentables = pagination($paymentables, $request->get('limit'));

        return new ApiCollection($paymentables);
    }
}
