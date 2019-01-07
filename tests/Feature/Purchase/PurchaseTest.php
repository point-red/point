<?php

namespace Tests\Feature\Purchase\PurchaseOrder;

use Tests\TestCase;
use ChartOfAccountSeeder;
use App\Model\Master\Item;
use Tests\RefreshDatabase;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use App\Model\Accounting\ChartOfAccountType;
use App\Model\HumanResource\Employee\Employee;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_purchase_test()
    {
        $employee = factory(Employee::class)->create();
        $supplier = factory(Supplier::class)->create();
        $warehouse = factory(Warehouse::class)->create();

        $this->artisan('tenant:seed', [
            'db_name' => 'point_tenant_test',
            'class' => ChartOfAccountSeeder::class,
        ]);
        $inventoryAccount = ChartOfAccountType::where('name', 'inventory')->first()->accounts->first();
        $items = factory(Item::class, 3)->create(
            ['chart_of_account_id' => $inventoryAccount->id]
        );

        $purchaseRequest = $this->createPurchaseRequest($employee, $supplier, $items);
        $this->createPurchaseOrder($purchaseRequest);
    }

    private function createPurchaseRequest($employee, $supplier, $items)
    {
        $data = [
            'employee_id' => $employee->id,
            'supplier_id' => $supplier->id,
            'date' => date('Y-m-d'),
            'required_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $items[0]->id,
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'converter' => 1,
                    'price' => 1000,
                    'description' => 'Test',
                ],
                [
                    'item_id' => $items[0]->id,
                    'quantity' => 5,
                    'unit' => 'pcs',
                    'converter' => 1,
                    'price' => 2000,
                    'description' => 'Test',
                ],
                [
                    'item_id' => $items[0]->id,
                    'quantity' => 7,
                    'unit' => 'pcs',
                    'converter' => 1,
                    'price' => 3000,
                    'description' => 'Test',
                ],
            ],
        ];

        // API Request
        $response = $this->json('POST', 'api/v1/purchase/purchase-requests', $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);

        // Check Database
        $this->assertDatabaseHas('forms', $response->json('data')['form'], 'tenant');
        $this->assertDatabaseHas('purchase_requests', [
            'required_date' => $response->json('data')['required_date'],
            'employee_id' => $response->json('data')['employee_id'],
            'supplier_id' => $response->json('data')['supplier_id'],
        ], 'tenant');

        return $response->json('data');
    }

    private function createPurchaseOrder($purchaseRequest)
    {
        $data = [
            'purchase_request_id' => $purchaseRequest['id'],
            'supplier_id' => $purchaseRequest['supplier_id'],
            'date' => date('Y-m-d'),
            'required_date' => date('Y-m-d'),
            'items' => [
                [
                    'purchase_request_item_id' => $purchaseRequest['items'][0]['id'],
                    'item_id' => $purchaseRequest['items'][0]['item_id'],
                    'quantity' => $purchaseRequest['items'][0]['quantity'],
                    'unit' => $purchaseRequest['items'][0]['unit'],
                    'converter' => $purchaseRequest['items'][0]['converter'],
                    'price' => $purchaseRequest['items'][0]['price'],
                    'description' => $purchaseRequest['items'][0]['description'],
                ],
                [
                    'purchase_request_item_id' => $purchaseRequest['items'][1]['id'],
                    'item_id' => $purchaseRequest['items'][1]['item_id'],
                    'quantity' => $purchaseRequest['items'][1]['quantity'],
                    'unit' => $purchaseRequest['items'][1]['unit'],
                    'converter' => $purchaseRequest['items'][1]['converter'],
                    'price' => $purchaseRequest['items'][1]['price'],
                    'description' => $purchaseRequest['items'][1]['description'],
                ],
                [
                    'purchase_request_item_id' => $purchaseRequest['items'][2]['id'],
                    'item_id' => $purchaseRequest['items'][2]['item_id'],
                    'quantity' => $purchaseRequest['items'][2]['quantity'],
                    'unit' => $purchaseRequest['items'][2]['unit'],
                    'converter' => $purchaseRequest['items'][2]['converter'],
                    'price' => $purchaseRequest['items'][2]['price'],
                    'description' => $purchaseRequest['items'][2]['description'],
                ],
            ],
        ];

        // API Request
        $response = $this->json('POST', 'api/v1/purchase/purchase-orders', $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);

        // Check Database
        $this->assertDatabaseHas('forms', $response->json('data')['form'], 'tenant');
    }
}
