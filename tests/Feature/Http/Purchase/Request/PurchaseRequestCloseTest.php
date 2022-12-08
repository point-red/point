<?php

namespace Tests\Feature\Http\Purchase\Request;

use Tests\TestCase;

class PurchaseRequestCloseTest extends TestCase
{
    use PurchaseRequestSetup;

    public function success_create_purchase_request()
    {
        $data = $this->createDataPurchaseRequest();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);

        // save data
        $this->purchase = json_decode($response->getContent())->data;
    }

    public function approve_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        $data = [
            'id' => $this->purchase->id
        ];

        $this->json('POST', self::$path.'/'.$this->purchase->id.'/approve', $data, $this->headers);
    }

    /** @test */
    public function invalid_data_close_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $data = [
            "id" => $this->purchase->id,
        ];
        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/close', $data, $this->headers);
        $response->assertStatus(422)->assertJson([
            "code" => 422,
            "message" => "The given data was invalid."
        ]);
    }

    /** @test */
    public function invalid_condition_close_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $data = [
            "id" => $this->purchase->id,
            "reason" => "sample reason"
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/close', $data, $this->headers);
        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Form not approved or not in pending state"
            ]);
    }

    /** @test */
    public function success_close_purchase_request()
    {
        $this->approve_purchase_request();

        $data = [
            "id" => $this->purchase->id,
            "reason" => "sample reason"
        ];
        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/close', $data, $this->headers);
        $response->assertStatus(204);

        $this->assertDatabaseHas('forms', [
            'number' => $this->purchase->form->number,
            'close_status' => true
        ], 'tenant');
    }

    // /** @test */
    // public function invalid_state_close_approve_purchase_request()
    // {
    //     //create purchase request and save to $this->purchase
    //     $this->success_create_purchase_request();

    //     $response = $this->json('POST', self::$path . '/' . $this->purchase->id . '/close-approve', [], $this->headers);
    //     $response->assertStatus(422)->assertJson([
    //         "code" => 422,
    //         "message" => "Form not approved or not in pending state"
    //     ]);
    // }

    // /** @test */
    // public function success_close_approve_purchase_request()
    // {
    //     $this->success_close_purchase_request();

    //     $response = $this->json('POST', self::$path . '/' . $this->purchase->id . '/close-approve', [], $this->headers);
    //     $response->assertStatus(200);
    // }

    // /** @test */
    // public function invalid_close_reject_purchase_request()
    // {
    //     $this->success_close_purchase_request();

    //     $response = $this->json('POST', self::$path . '/' . $this->purchase->id . '/close-reject', [], $this->headers);
    //     $response->assertStatus(422);
    // }

    // /** @test */
    // public function invalid_state_close_reject_purchase_request()
    // {
    //     //create purchase request and save to $this->purchase
    //     $this->success_create_purchase_request();

    //     $data["reason"] = "reject";
    //     $response = $this->json('POST', self::$path . '/' . $this->purchase->id . '/close-approve', $data, $this->headers);
    //     $response->assertStatus(422);
    // }

    // /** @test */
    // public function success_reject_purchase_request()
    // {
    //     $this->success_close_purchase_request();

    //     $data['reason'] = $this->faker->text(200);
    //     $response = $this->json('POST', self::$path . '/' . $this->purchase->id . '/close-reject', $data, $this->headers);

    //     $response->assertStatus(200);
    // }

    /** @test */
    public function success_autoclose_purchase_request()
    {
        // $this->expectOutputString('');
        $data = $this->createDataPurchaseRequest();
        foreach($data['items'] as $key=>$item){
            $data['items'][$key]['quantity_remaining'] = 0;
        }
        
        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(201);

        // save data
        $this->purchase = json_decode($response->getContent())->data;

        $this->assertDatabaseHas('forms', [
            'number' => $this->purchase->form->number,
            'close_status' => true,
            'close_approval_reason' => 'Closed by system'
        ], 'tenant');
    }
}
