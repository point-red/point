<?php

namespace Tests\Feature\Http\Purchase\Request;

use Tests\TestCase;
use App\Model\Token;

class PurchaseRequestApprovalTest extends TestCase
{
    use PurchaseRequestSetup;

    public function success_create_purchase_request()
    {
        $data = $this->createDataPurchaseRequest();
        
        $response = $this->json('POST', self::$path, $data, $this->headers);

        // save data
        $this->purchase = json_decode($response->getContent())->data;
    }

    /** @test */
    public function unauthorized_reject_purchase_request()
    {
        $this->success_create_purchase_request();
        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/' . $this->purchase->id . '/reject', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
            ]);
    }

    /** @test */
    public function reject_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        /* s: reject test */
        $data = [
            'id' => $this->purchase->id,
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/reject', $data, $this->headers);
        $response->assertStatus(200);
        /* e: reject test */
    }

    /** @test */
    public function unauthorized_approve_purchase_request()
    {
        $this->success_create_purchase_request();
        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/' . $this->purchase->id . '/approve', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Unauthorized"
            ]);
    }

    /** @test */
    public function approve_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();
        /* s: reject test */
        $data = [
            'id' => $this->purchase->id
        ];

        $response = $this->json('POST', self::$path.'/'.$this->purchase->id.'/approve', $data, $this->headers);
        $response->assertStatus(200);
        /* e: reject test */
    }

    /** @test */
    public function failed_request_approval_by_email_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        $response = $this->json('POST', self::$path.'/send-bulk-request-approval', [], $this->headers);
        $response->assertStatus(422);
    }

    /** @test */
    public function success_request_approval_by_email_purchase_request()
    {
        //create purchase request and save to $this->purchase
        $this->success_create_purchase_request();

        /* s: send request approval email */
        $data = [
            'bulk_id'=> array($this->purchase->id),
            'tenant_url' => 'http://dev.localhost:8080'
        ];

        $response = $this->json('POST', self::$path.'/send-bulk-request-approval', $data, $this->headers);
        $response->assertStatus(204);
        /* e: send request approval email */
    }

    /** @test */
    public function failed_approval_by_email_purchase_request()
    {
        $this->success_request_approval_by_email_purchase_request();

        /* s: bulk approval email fail test */
        $data = [
            'token' => 'NGAWUR', 
            'bulk_id' => array($this->purchase->id), 
            'status' => -1
        ];

        $response = $this->json('POST', self::$path.'/approval-with-token/bulk', $data, $this->headers);
        $response->assertStatus(422);
        /* e: bulk approval email fail test */
    }

    /** @test */
    public function success_approval_by_email_purchase_request()
    {
        $this->success_request_approval_by_email_purchase_request();

        /* s: bulk approval email test */
        $token = Token::take(1)->first();
        $data = [
            'token' => $token->token, 
            'bulk_id' => array($this->purchase->id), 
            'status' => -1
        ];

        $response = $this->json('POST', self::$path.'/approval-with-token/bulk', $data, $this->headers);
        $response->assertStatus(200);
        /* e: bulk approval email test */
    }

}