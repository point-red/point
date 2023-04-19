<?php

namespace Tests\Feature\Http\Finance\Cash;

use App\Model\Finance\Payment\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CashOutCancellationTest extends TestCase
{
    use CashOutSetup;

    public static $path = '/api/v1/finance';

    public function createPayment()
    {
        $paymentOrder = $this->createPaymentOrder();
        $data = $this->getDataPayment($paymentOrder);

        $this->json('POST', self::$path . '/payments', $data, $this->headers);

        return Payment::orderBy('id', 'desc')->first();
    }

    // Success request to delete
    /** @test */
    public function success_request_to_delete()
    {
        $data = [
            'reason' => 'Please delete this form because...'
        ];

        $payment = $this->createPayment();

        $response = $this->json('DELETE', self::$path . '/payments/' . $payment->id, $data, $this->headers);
        $response->assertStatus(204);
    }

    // Fail request to delete because didn't insert reason
    /** @test */
    public function fail_request_to_delete()
    {
        $payment = $this->createPayment();

        $response = $this->json('DELETE', self::$path . '/payments/' . $payment->id, $this->headers);
        $response->assertStatus(422);
    }

    public function getPaymentReadyToCancellation()
    {
        $data = [
            'reason' => 'Please delete this form because...'
        ];

        $payment = $this->createPayment();
        $this->json('DELETE', self::$path . '/payments/' . $payment->id, $data, $this->headers);

        return Payment::orderBy('id', 'desc')->first();
    }

    // Success approve to delete
    /** @test */
    public function success_approve_to_delete()
    {
        $payment = $this->getPaymentReadyToCancellation();

        $response = $this->json('POST', self::$path . '/payments/' . $payment->id . '/cancellation-approve', $this->headers);
        $response->assertStatus(200);
    }

    // Success reject to delete
    /** @test */
    public function success_reject_to_delete()
    {
        $data = [
            'reason' => 'Sorry, this form can\'t be deleted'
        ];

        $payment = $this->getPaymentReadyToCancellation();

        $response = $this->json('POST', self::$path . '/payments/' . $payment->id . '/cancellation-reject', $data, $this->headers);
        $response->assertStatus(200);
    }

    // Fail reject to delete
    /** @test */
    public function fail_reject_to_delete()
    {
        $payment = $this->getPaymentReadyToCancellation();

        $response = $this->json('POST', self::$path . '/payments/' . $payment->id . '/cancellation-reject', $this->headers);
        $response->assertStatus(422);
    }
}
