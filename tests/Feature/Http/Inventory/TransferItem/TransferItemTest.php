<?php

namespace Tests\Feature\Http\Inventory\TransferItem;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Imports\Template\ChartOfAccountImport;
use App\Model\Master\Item;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Warehouse;
use App\Model\Inventory\TransferItem\TransferItem;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class TransferItemTest extends TestCase
{
    public static $path = '/api/v1/inventory/transfer-items';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setProject();
        $this->importChartOfAccount();
    }

    private function importChartOfAccount()
    {
        Excel::import(new ChartOfAccountImport(), storage_path('template/chart_of_accounts_manufacture.xlsx'));


        $this->artisan('db:seed', [
            '--database' => 'tenant',
            '--class' => 'SettingJournalSeeder',
            '--force' => true,
        ]);
    }

    public function dummyData()
    {
        $item     = factory(Item::class)->create();

        $warehouse = factory(Warehouse::class)->create();
        $to_warehouse = factory(Warehouse::class)->create();

        $user = new TenantUser;
        $user->name = $this->faker->name;
        $user->address = $this->faker->address;
        $user->phone = $this->faker->phoneNumber;
        $user->email = $this->faker->email;
        $user->save();

        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "warehouse_id" => $warehouse->id,
            "to_warehouse_id" => $to_warehouse->id,
            "driver" => "Some one",
            "request_approval_to" => $user->id,
            "items" => [
                [
                    "item_id" => $item->id,
                    "item_name" => $item->name,
                    "unit" => "PCS",
                    "converter" => 1,
                    "quantity" => 10,
                    "stock" => 30,
                    "balance" => 20,
                    "warehouse_id" => $warehouse->id,
                    'dna' => [
                        [
                            "quantity" => 10,
                            "item_id" => $item->id,
                            "expiry_date" => date('Y-m-d', strtotime('1 year')),
                            "production_number" => "sample",
                            "remaining" => 30,
                        ]
                    ]
                ]
            ]
        ];

        return $data;
    }

    /** @test */
    public function create_transfer_item()
    {
        $data = $this->dummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
    }

    /**
     * @test 
     */
    public function read_all_transfer_item()
    {
        $response = $this->json('GET', self::$path, [
            'join' => 'form,items,item',
            'fields' => 'transfer_sent.*',
            'group_by' => 'form.id',
            'sort_by' => '-form.number',
        ], $this->headers);

        $response->assertStatus(200);
    }

    /**
     * @test 
     */
    public function read_single_transfer_item()
    {
        $this->create_transfer_item();

        $transferItem = TransferItem::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$transferItem->id, [
            'includes' => 'warehouse;to_warehouse;items.item;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function update_transfer_item()
    {
        $this->create_transfer_item();

        $transferItem = TransferItem::orderBy('id', 'asc')->first();

        $data = $this->dummyData();

        $data["id"] = $transferItem->id;

        $response = $this->json('PATCH', self::$path.'/'.$transferItem->id, $data, [$this->headers]);

        $response->assertStatus(201);
    }

    /** @test */
    public function delete_transfer_item()
    {
        $this->create_transfer_item();

        $transferItem = TransferItem::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$transferItem->id, [], [$this->headers]);

        $response->assertStatus(204);
    }

    /** @test */
    public function export_transfer_item_success()
    {
        $this->create_transfer_item();

        $transferItem = TransferItem::orderBy('id', 'asc')->first();

        $data = [
            "data" => [
                "ids" => [$transferItem->id],
                "date_start" => date("Y-m-d", strtotime("-1 days")),
                "date_end" => date("Y-m-d", strtotime("+1 days"))
            ]
        ];

        $response = $this->json('POST', self::$path.'/export', $data, $this->headers);
        
        $response->assertStatus(200);
    }
}
