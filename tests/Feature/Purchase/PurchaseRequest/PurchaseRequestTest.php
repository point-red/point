<?php

namespace Tests\Feature\Purchase\PurchaseRequest;

use App\Model\Accounting\ChartOfAccountType;
use App\Model\HumanResource\Employee\Employee;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use ChartOfAccountSeeder;
use Tests\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class PurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_purchase_request_test()
    {
        $employee = factory(Employee::class)->create();
        $supplier = factory(Supplier::class)->create();
        $this->artisan('tenant:seed', [
            'db_name' => 'point_tenant_test',
            'class' => ChartOfAccountSeeder::class,
        ]);
        $inventoryAccount = ChartOfAccountType::where('name', 'inventory')->first()->accounts->first();
        $item = factory(Item::class)->create(
            ['chart_of_account_id' => $inventoryAccount->id]
        );

        $data = [
            'employee_id' => $employee->id,
            'supplier_id' => $supplier->id,
            'date' => date('Y-m-d'),
            'required_date' => date('Y-m-d'),
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                    'unit' => 'pcs',
                    'converter' => 1,
                    'price' => 1000,
                    'description' => 'Test',
                ]
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
    }

    /** @test */
    public function create_purchase_request_without_know_supplier()
    {
        $employee = factory(Employee::class)->create();

        $data = [
            'employee_id' => $employee->id,
            'date' => date('Y-m-d'),
            'required_date' => date('Y-m-d'),
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
        ], 'tenant');
    }
}
