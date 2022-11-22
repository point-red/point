<?php

namespace Tests\Feature\Http\Purchase\PurchaseReceive;

use App\Model\Auth\Permission;
use App\Model\Purchase\PurchaseReceive\PurchaseReceive;
use App\Model\Purchase\PurchaseRequest\PurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseReceiveControllerTest extends TestCase
{
    use PurchaseReceiveSetup;

    public function createPurchaseReceiveBranchNotDefault()
    {
        $this->setStock(300);
        $this->setRole();

        $data = $this->getDummyData();

        $this->unsetDefaultBranch();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'please set default branch to create this form',
            ]);
    }


    /** @test */
    public function createPurchaseReceiveFailed()
    {
        $this->setRole();

        $dummy = $this->getDummyData();
        $data = ['warehouse_id' => $dummy['warehouse_id'], 'items' => $dummy['items']];

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
            ]);
    }


    /** @test */
    public function createPurchaseReceiveQuantityZero()
    {
        $this->setRole();

        $data = $this->getDummyData();
        $data['items'][0] = data_set($data['items'][0], 'quantity', 0);
        $data['items'][0]['dna'][0] = data_set($data['items'][0]['dna'][0], 'quantity', 0);

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'total_quantity' => [
                        'quantity must be filled in',
                    ],
                ],
            ]);
    }
}
