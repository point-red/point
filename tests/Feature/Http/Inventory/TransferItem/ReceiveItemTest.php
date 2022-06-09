<?php

namespace Tests\Feature\Http\Inventory\TransferItem;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Master\Item;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Warehouse;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\Inventory\TransferItem\ReceiveItem;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReceiveItemTest extends TestCase
{
    public static $path = '/api/v1/inventory/receive-items';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
        $this->setProject();
        $this->importChartOfAccount();
        $_SERVER['HTTP_REFERER'] = 'http://www.example.com/';
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

    public function dummyDataTransferItem()
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

    public function create_transfer_item()
    {
        $data = $this->dummyDataTransferItem();
        
        $this->json('POST', '/api/v1/inventory/transfer-items', $data, $this->headers);
    }

    public function dummyDataReceiveItem()
    {
        $this->create_transfer_item();

        $transferItem = TransferItem::orderBy('id', 'asc')->first();

        $warehouse = Warehouse::findOrFail($transferItem->to_warehouse_id);
        $from_warehouse = Warehouse::findOrFail($transferItem->warehouse_id);

        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => $transferItem->form->notes,
            "warehouse_id" => $warehouse->id,
            "from_warehouse_id" => $from_warehouse->id,
            "request_approval_to" => $transferItem->form->request_approval_to,
            "transfer_item_id" => $transferItem->id,
            "items" => [
                [
                    "item_id" => $transferItem->items[0]->item_id,
                    "item_name" => $transferItem->items[0]->item_name,
                    "unit" => $transferItem->items[0]->unit,
                    "converter" => $transferItem->items[0]->converter,
                    "quantity" => $transferItem->items[0]->quantity,
                    "stock" => 50,
                    "balance" => 60,
                    "warehouse_id" => $warehouse->id,
                    "expiry_date" => $transferItem->items[0]->expiry_date,
                    "production_number" => $transferItem->items[0]->production_number,
                ]
            ]
        ];

        return $data;
    }

    /** @test */
    public function create_receive_item()
    {
        $data = $this->dummyDataReceiveItem();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
    }

    /**
     * @test 
     */
    public function read_all_receive_item()
    {
        $response = $this->json('GET', self::$path, [
            'join' => 'form,items,item',
            'fields' => 'transfer_receive.*',
            'group_by' => 'form.id',
            'sort_by' => '-form.number',
        ], $this->headers);

        $response->assertStatus(200);
    }

    /**
     * @test 
     */
    public function read_single_receive_item()
    {
        $this->create_receive_item();

        $receiveItem = ReceiveItem::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$receiveItem->id, [
            'includes' => 'warehouse;from_warehouse;items.item;form.createdBy;form.requestApprovalTo;form.branch'
        ], $this->headers);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function update_receive_item()
    {
        $this->create_receive_item();

        $receiveItem = ReceiveItem::orderBy('id', 'asc')->first();

        $data = $this->dummyDataReceiveItem();

        $data["id"] = $receiveItem->id;

        $response = $this->json('PATCH', self::$path.'/'.$receiveItem->id, $data, [$this->headers]);

        $response->assertStatus(201);
    }

    /** @test */
    public function delete_receive_item()
    {
        $this->create_receive_item();

        $receiveItem = ReceiveItem::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$receiveItem->id, [], [$this->headers]);

        $response->assertStatus(204);
    }

    /** @test */
    public function export_receive_item_success()
    {
        $this->create_receive_item();

        $receiveItem = ReceiveItem::orderBy('id', 'asc')->first();

        $data = [
            "data" => [
                "ids" => [$receiveItem->id],
                "date_start" => date("Y-m-d", strtotime("-1 days")),
                "date_end" => date("Y-m-d", strtotime("+1 days")),
                "tenant_name" => "development"
            ]
        ];

        $response = $this->json('POST', self::$path.'/export', $data, $this->headers);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function send_receive_item_approval()
    {
        $this->create_receive_item();

        $receiveItem = ReceiveItem::orderBy('id', 'asc')->first();

        $data = [
            "id" => $receiveItem->id,
            "form_send_done" => 1,
            "crud_type" => "update"
        ];

        $response = $this->json('POST', self::$path.'/'.$receiveItem->id.'/send', $data, $this->headers);
        
        $response->assertStatus(200);
    }
}
