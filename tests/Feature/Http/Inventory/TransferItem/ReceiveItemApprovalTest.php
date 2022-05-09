<?php

namespace Tests\Feature\Http\Inventory\TransferItem;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Master\Item;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Warehouse;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Form;
use App\Model\Inventory\TransferItem\TransferItem;
use App\Model\Inventory\TransferItem\ReceiveItem;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReceiveItemApprovalTest extends TestCase
{
    public static $path = '/api/v1/inventory/approval/receive-items';

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

    public function dummyData($item)
    {
        
        $warehouse = factory(Warehouse::class)->create();
        $to_warehouse = factory(Warehouse::class)->create();

        $distributionWarehouse = new Warehouse();
        $distributionWarehouse->name = 'DISTRIBUTION WAREHOUSE';
        $distributionWarehouse->save();

        $user = new TenantUser;
        $user->name = $this->faker->name;
        $user->address = $this->faker->address;
        $user->phone = $this->faker->phoneNumber;
        $user->email = $this->faker->email;
        $user->save();

        $form = new Form;
        $form->date = now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();

        $options = [];
        if ($item->require_expiry_date) {
            $options['expiry_date'] = $item->expiry_date;
        }
        if ($item->require_production_number) {
            $options['production_number'] = $item->production_number;
        }

        $options['quantity_reference'] = $item->quantity;
        $options['unit_reference'] = $item->unit;
        $options['converter_reference'] = $item->converter;

        InventoryHelper::increase($form, $warehouse, $item, 100, "PCS", 1, $options);
        
        $data = [
            "date" => now()->timezone('Asia/Jakarta')->toDateTimeString(),
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
                    "stock" => 100,
                    "balance" => 80,
                    "warehouse_id" => $warehouse->id,
                    'dna' => [
                        [
                            "quantity" => 10,
                            "item_id" => $item->id,
                            "expiry_date" => date('Y-m-d', strtotime('1 year')),
                            "production_number" => "sample",
                            "remaining" => 100,
                        ]
                    ]
                ]
            ]
        ];

        return $data;
    }

    public function create_receive_item()
    {
        $coa = ChartOfAccount::orderBy('id', 'desc')->first();
        
        $item = new Item;
        $item->name = $this->faker->name;
        $item->chart_of_account_id = $coa->id;
        $item->save();

        $data = $this->dummyData($item);

        $this->json('POST', '/api/v1/inventory/transfer-items', $data, $this->headers);

        $transferItem = TransferItem::orderBy('id', 'asc')->first();

        $warehouse = Warehouse::findOrFail($transferItem->to_warehouse_id);
        $from_warehouse = Warehouse::findOrFail($transferItem->warehouse_id);

        $distributionWarehouse = Warehouse::where('name', 'DISTRIBUTION WAREHOUSE')->first();
        
        $form = new Form;
        $form->date = now()->toDateTimeString();
        $form->created_by = $this->user->id;
        $form->updated_by = $this->user->id;
        $form->save();
        
        $item = Item::findOrFail($transferItem->items[0]->item_id);
        
        $options = [];
        if ($item->require_expiry_date) {
            $options['expiry_date'] = $item->expiry_date;
        }
        if ($item->require_production_number) {
            $options['production_number'] = $item->production_number;
        }

        $options['quantity_reference'] = $item->quantity;
        $options['unit_reference'] = $item->unit;
        $options['converter_reference'] = $item->converter;

        InventoryHelper::increase($form, $distributionWarehouse, $item, 100, "PCS", 1, $options);

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

        $this->json('POST', '/api/v1/inventory/receive-items', $data, $this->headers);
    }

    /**
     * @test 
     */
    public function approve_receive_item()
    {
        $this->create_receive_item();

        $receiveItem = ReceiveItem::orderBy('id', 'asc')->first();

        $response = $this->json('POST', '/api/v1/inventory/receive-items/'.$receiveItem->id.'/approve', [
            'id' => $receiveItem->id,
            'form_send_done' => 1
        ], $this->headers);
        
        $response->assertStatus(200);
    }

    /**
     * @test 
     */
    public function reject_transfer_item()
    {
        $this->create_receive_item();

        $receiveItem = ReceiveItem::orderBy('id', 'asc')->first();

        $response = $this->json('POST', '/api/v1/inventory/transfer-items/'.$receiveItem->id.'/reject', [
            'id' => $receiveItem->id,
            'reason' => 'some reason'
        ], $this->headers);
        
        $response->assertStatus(200);
    }
}
