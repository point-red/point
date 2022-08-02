<?php

namespace Tests\Feature\Http\Accounting;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Master\Item;
use App\Model\Master\User as TenantUser;
use App\Model\Master\Warehouse;
use App\Helpers\Inventory\InventoryHelper;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\MemoJournal;
use App\Model\Master\Supplier;
use App\Model\Form;
use App\Model\Inventory\TransferItem\TransferItem;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class MemoJournalApprovalTest extends TestCase
{
    public static $path = '/api/v1/accounting/approval/memo-journals';

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

    // public function dummyData($item)
    // {
        
    //     $warehouse = factory(Warehouse::class)->create();
    //     $to_warehouse = factory(Warehouse::class)->create();

    //     $user = new Warehouse();
    //     $user->name = 'DISTRIBUTION WAREHOUSE';
    //     $user->save();

    //     $user = new TenantUser;
    //     $user->name = $this->faker->name;
    //     $user->address = $this->faker->address;
    //     $user->phone = $this->faker->phoneNumber;
    //     $user->email = $this->faker->email;
    //     $user->save();

    //     $form = new Form;
    //     $form->date = now()->toDateTimeString();
    //     $form->created_by = $this->user->id;
    //     $form->updated_by = $this->user->id;
    //     $form->save();

    //     $options = [];
    //     if ($item->require_expiry_date) {
    //         $options['expiry_date'] = $item->expiry_date;
    //     }
    //     if ($item->require_production_number) {
    //         $options['production_number'] = $item->production_number;
    //     }

    //     $options['quantity_reference'] = $item->quantity;
    //     $options['unit_reference'] = $item->unit;
    //     $options['converter_reference'] = $item->converter;

    //     InventoryHelper::increase($form, $warehouse, $item, 100, "PCS", 1, $options);
        
    //     $data = [
    //         "date" => now()->timezone('Asia/Jakarta')->toDateTimeString(),
    //         "increment_group" => date("Ym"),
    //         "notes" => "Some notes",
    //         "warehouse_id" => $warehouse->id,
    //         "to_warehouse_id" => $to_warehouse->id,
    //         "driver" => "Some one",
    //         "request_approval_to" => $user->id,
    //         "items" => [
    //             [
    //                 "item_id" => $item->id,
    //                 "item_name" => $item->name,
    //                 "unit" => "PCS",
    //                 "converter" => 1,
    //                 "quantity" => 10,
    //                 "stock" => 100,
    //                 "balance" => 80,
    //                 "warehouse_id" => $warehouse->id,
    //                 'dna' => [
    //                     [
    //                         "quantity" => 10,
    //                         "item_id" => $item->id,
    //                         "expiry_date" => date('Y-m-d', strtotime('1 year')),
    //                         "production_number" => "sample",
    //                         "remaining" => 100,
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ];

    //     return $data;
    // }

    public function dummyData()
    {
        $coa1 = ChartOfAccount::orderBy('id', 'asc')->first();
        $coa2 = ChartOfAccount::orderBy('id', 'desc')->first();
        
        $supplier = factory(Supplier::class)->create();

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

        $data = [
            "date" => date("Y-m-d H:i:s"),
            "increment_group" => date("Ym"),
            "notes" => "Some notes",
            "request_approval_to" => $user->id,
            "items" => [
                [
                    "chart_of_account_id" => $coa1->id,
                    "chart_of_account_name" => $coa1->name,
                    "masterable_id" => $supplier->id,
                    "masterable_type" => "Supplier",
                    "form_id" => $form->id,
                    "credit" => 0,
                    "debit" => 100000,
                    "notes" => "note 1",
                ],
                [
                    "chart_of_account_id" => $coa2->id,
                    "chart_of_account_name" => $coa2->name,
                    "masterable_id" => null,
                    "masterable_type" => null,
                    "form_id" => null,
                    "credit" => 100000,
                    "debit" => 0,
                    "notes" => "note 2",
                ]
            ]
        ];

        return $data;
    }

    /**
     * @test 
     */
    public function read_all_memo_journal_approval()
    {
        $response = $this->json('GET', self::$path, [
            'limit' => '10',
            'page' => '1',
        ], $this->headers);

        $response->assertStatus(200);
    }

    /**
     * @test 
     */
    public function approve_memo_journal()
    {
        $data = $this->dummyData();

        $this->json('POST', '/api/v1/accounting/memo-journals', $data, $this->headers);

        $memoJournal = MemoJournal::orderBy('id', 'asc')->first();

        $response = $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->id.'/approve', [
            'id' => $memoJournal->id
        ], $this->headers);
        
        $response->assertStatus(200);
    }

    /**
     * @test 
     */
    public function reject_memo_journal()
    {
        $data = $this->dummyData();

        $this->json('POST', '/api/v1/accounting/memo-journals', $data, $this->headers);

        $memoJournal = MemoJournal::orderBy('id', 'desc')->first();

        $response = $this->json('POST', '/api/v1/accounting/memo-journals/'.$memoJournal->id.'/reject', [
            'id' => $memoJournal->id,
            'reason' => 'some reason'
        ], $this->headers);
        
        $response->assertStatus(200);
    }

    /** @test */
    public function send_memo_journal_approval()
    {
        $data = $this->dummyData();

        $this->json('POST', '/api/v1/accounting/memo-journals', $data, $this->headers);

        $memoJournal = MemoJournal::orderBy('id', 'desc')->first();

        $data = [
            "ids" => [
                "id" => $memoJournal->id,
            ],
        ];

        $response = $this->json('POST', self::$path.'/send', $data, $this->headers);
        
        $response->assertStatus(200);
    }
}
