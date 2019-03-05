<?php

namespace App\Http\Controllers\Api\Sales\SalesInvoice;

use App\Model\Form;
use Illuminate\Http\Request;
use App\Model\Accounting\Journal;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ApiResource;
use App\Http\Controllers\Controller;
use App\Http\Resources\ApiCollection;
use App\Model\Sales\SalesInvoice\SalesInvoice;

class SalesInvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return ApiCollection
     */
    public function index(Request $request)
    {
        $salesInvoices = SalesInvoice::eloquentFilter($request)
            ->joinForm()
            ->notArchived()
            ->when($request->get('remaining_info'), function ($query) use ($request) {
                $journalPayment = Journal::selectRaw('SUM(IFNULL(credit, 0)) AS credit')
                    ->addSelect('form_id_reference')
                    ->where(Journal::getTableName('chart_of_account_id'), $request->get('coa_invoice'))
                    ->where('credit', '>', 0);

                $query->leftJoinSub($journalPayment, 'journal_payment', function ($join) {
                    $join->on(Form::getTableName('id'), '=', 'journal_payment.form_id_reference');
                })
                    ->addSelect(\DB::raw('IFNULL(journal_payment.credit, 0) AS paid'))
                    ->addSelect(\DB::raw(SalesInvoice::getTableName('amount') . ' - IFNULL(journal_payment.credit, 0) AS remaining'));
            })
            ->with('form');

        $salesInvoices = pagination($salesInvoices, $request->get('limit'));

        return new ApiCollection($salesInvoices);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $result = DB::connection('tenant')->transaction(function () use ($request) {
            $salesInvoice = SalesInvoice::create($request->all());

            $salesInvoice
                ->load('form')
                ->load('customer')
                ->load('items.item')
                ->load('items.allocation')
                ->load('services.service')
                ->load('services.allocation');

            return new ApiResource($salesInvoice);
        });

        return $result;
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
        $salesInvoice = SalesInvoice::eloquentFilter($request)
            ->with('form')
            ->with('customer')
            ->with('items.item')
            ->with('items.allocation')
            ->with('items.deliveryNote.form')
            ->with('services.service')
            ->with('services.allocation')
            ->findOrFail($id);

        return new ApiResource($salesInvoice);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int  $id
     * @return ApiResource
     */
    public function update(Request $request, $id)
    {
        // TODO prevent delete if referenced by delivery notes
        $result = DB::connection('tenant')->transaction(function () use ($request, $id) {
            $salesInvoice = SalesInvoice::findOrFail($id);

            $newSalesInvoice = $salesInvoice->edit($request->all());

            return new ApiResource($newSalesInvoice);
        });

        return $result;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $salesInvoice = SalesInvoice::findOrFail($id);

        $salesInvoice->delete();

        return response()->json([], 204);
    }
}
