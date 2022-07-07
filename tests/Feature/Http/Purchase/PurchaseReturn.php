<?php

namespace Tests\Feature\Http\PurchaseReturn;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseReturn extends TestCase
{
    private $route = "/api/v1/return";

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function invoiceDetail()
    {
        $response = $this->get($this->route . '/invoice/1');
        $response->assertStatus(200);
    }

    /** @test */
    public function userApproverList()
    {
        $response = $this->get($this->route . '/user/approver/list');
        $response->assertStatus(200);
    }

    /** @test */
    public function createReturn()
    {
        $payload = [
            "date" => date('Y-m-d'),
            "invoice_id" => 1,
            "warehouse_id" => 1,
            "items" => [
                [
                    "id" => 1,
                    "qty_return" => 3,
                    "invetories_id" => [
                        1, 2, 3, 4, 5
                    ]
                ]
            ],
            "notes" => "Notes",
            "approved_by" => 1,
        ];
        $response = $this->post($this->route . '/user/approver/list', $payload);
        $response->assertStatus(200);
    }

    /** @test */
    public function listReturn()
    {
        $param = "?limit=5&search=tes&date_from=2020-02-02&date_to=2021-02-02&approval_status=approved&form_status=pending";
        $response = $this->get($this->route . '/' . $param);
        $response->assertStatus(200);
    }

    /** @test */
    public function detailReturn()
    {
        $response = $this->get($this->route . '/1');
        $response->assertStatus(200);
    }

    /** @test */
    public function updateReturn()
    {
        $response = $this->put($this->route . '/2');
        $response->assertStatus(200);
    }

    /** @test */
    public function aproveReturn()
    {
        $response = $this->put($this->route . '/2/approve');
        $response->assertStatus(200);
    }

    /** @test */
    public function rejectReturn()
    {
        $response = $this->put($this->route . '/2/reject');
        $response->assertStatus(200);
    }

    /** @test */
    public function sendEmailReturn()
    {
        $payload = [
            "purchase_return_id" => 1,
            "receipent" => "tes@gmail.com",
            "message" => "tes"
        ];
        $response = $this->put($this->route . '/2/send/email',$payload);
        $response->assertStatus(200);
    }

    /** @test */
    public function downloadReturn()
    {
        $response = $this->put($this->route . '/2/download');
        $response->assertStatus(200);
    }

    /** @test */
    public function historyListReturn()
    {
        $param="?limit=5";
        $response = $this->put($this->route . '/history/list'.$param);
        $response->assertStatus(200);
    }

    /** @test */
    public function archieveReturn()
    {
        $response = $this->get($this->route . '/1/archieve');
        $response->assertStatus(200);
    }

    /** @test */
    public function requestApproveAllReturn()
    {
        $payload = [
            [
                "id" => 1
            ]
        ];
        $response = $this->put($this->route . '/request/approval/all', $payload);
        $response->assertStatus(200);
    }
}
