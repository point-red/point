<?php

namespace Tests\Feature\Http\Finance\Cash;

use App\Model\Finance\Payment\Payment;
use Tests\TestCase;

class CashOutTest extends TestCase
{
    use CashOutSetup;

    public static $path = '/api/v1/finance';

    // Test success get all cash outs
    /** @test */
    public function success_get_all_cash_outs()
    {
        $this->success_cash_out_from_payment_order_with_cash_advance_and_account();

        $response = $this->json('GET', self::$path . '/payments?join=form,payment_account,details,account,allocation&sort_by=-form.date&fields=payment.*&filter_form=notArchived%3Bnull&filter_like=%7B%7D&filter_equal=%7B%22payment.payment_type%22:%22cash%22%7D&filter_date_min=%7B%22form.date%22:%22' . date('Y-m-01') . '+00:00:00%22%7D&filter_date_max=%7B%22form.date%22:%22' . date('Y-m-d') . '+23:59:59%22%7D&limit=10&includes=form%3Bdetails.chartOfAccount%3Bdetails.allocation%3Bpaymentable&page=1', $this->headers);
        $response->assertStatus(200);
    }

    // Test success get a cash out
    /** @test */
    public function success_get_a_cash_out()
    {
        $this->success_cash_out_from_payment_order_without_cash_advance();
        $payment = Payment::orderBy('id', 'desc')->first();
        $data = [
            'includes' => 'form.branch;paymentAccount;details.chartOfAccount;details.allocation'
        ];
        $response = $this->json('GET', self::$path . '/payments/' . $payment->id, $data, $this->headers);
        $response->assertStatus(200);
    }

    // Test create payment order for reference cash out
    /** @test */
    public function success_create_payment_order()
    {
        $data = $this->createPaymentOrder(true);
        $response = $this->json('POST', self::$path . '/payment-orders', $data, $this->headers);
        $response->assertStatus(201);

        $this->assertDatabaseHas('payment_orders', [
            'id' => $response->json('data.id')
        ], 'tenant')
            ->assertDatabaseHas('forms', [
                'id' => $response->json('data.form.id')
            ], 'tenant');

        foreach ($data['details'] as $detail) {
            unset($detail['chart_of_account_name']);
            $this->assertDatabaseHas('payment_order_details', $detail, 'tenant');
        }
    }

    // Test cash out from payment order without cash advance
    /** @test */
    public function success_cash_out_from_payment_order_without_cash_advance()
    {
        $paymentOrder = $this->createPaymentOrder();
        $data = $this->getDataPayment($paymentOrder);

        $response = $this->json('POST', self::$path . '/payments', $data, $this->headers);
        $response->assertStatus(201);

        $this->paymentAssertDatabaseHas($response, $data);
    }

    // Test cash out from payment order with cash advance and account
    /** @test */
    public function success_cash_out_from_payment_order_with_cash_advance_and_account()
    {
        $paymentOrder = $this->createPaymentOrder();
        $data = $this->getDataPayment($paymentOrder);
        $cashAdvance = $this->createCashAdvance($paymentOrder->amount - 10_000);

        $data['cash_advance'] = [
            'id' => $cashAdvance->id,
            'close' => false
        ];

        $response = $this->json('POST', self::$path . '/payments', $data, $this->headers);
        $response->assertStatus(201);

        $this->paymentAssertDatabaseHas($response, $data);
    }

    // Test cash out from payment order with cash advance only
    /** @test */
    public function success_cash_out_from_payment_order_with_cash_advance_only()
    {
        $paymentOrder = $this->createPaymentOrder();
        $data = $this->getDataPayment($paymentOrder);
        $cashAdvance = $this->createCashAdvance($paymentOrder->amount);

        $data['cash_advance'] =  [
            'id' => $cashAdvance->id,
            'close' => false
        ];

        $response = $this->json('POST', self::$path . '/payments', $data, $this->headers);
        $response->assertStatus(201);

        $this->paymentAssertDatabaseHas($response, $data);
    }
}
