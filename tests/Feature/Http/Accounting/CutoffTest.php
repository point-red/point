<?php

namespace Tests\Feature\Http\Accounting;

use App\Imports\Template\ChartOfAccountImport;
use App\Model\Accounting\ChartOfAccount;
use App\Model\Accounting\CutOffAccount;
use App\Model\Master\FixedAsset;
use App\Model\Master\Item;
use App\Model\Master\Supplier;
use App\Model\Master\Warehouse;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CutoffTest extends TestCase
{
    public static $path = '/api/v1/accounting/cut-offs';

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

    /**
     * @group ramadhani 
     * @test 
     */
    public function storeDataPayment()
    {
        $chartOfAccount = ChartOfAccount::with('type')->where([
            'sub_ledger' => 'CUSTOMER',
        ])->first();
        $data = [
            'date' => date("Y-m-d H:i:s"),
            'increment_group' => date("Ym"),
            'notes' => "Some notes",
            'details' => [
                [
                    'chart_of_account_id' => $chartOfAccount->id,
                    'chart_of_account_sub_ledger' => $chartOfAccount->sub_ledger,
                    'chart_of_account_type' => [
                        'name' => $chartOfAccount->type->name
                    ],
                    'debit' => $chartOfAccount->position == 'DEBIT' ? 500000 : 0,
                    'credit' => $chartOfAccount->position == 'CREDIT' ? 500000 : 0,
                    'items' => [
                        [
                            'object_id' => 1,
                            'amount' => 500000,
                            'date' => '2023-01-01',
                            'notes' => "Items level notes'"
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(201);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function storeDataDownPayment()
    {
        $chartOfAccount = ChartOfAccount::with('type')->whereHas('type', function($q) {
            return $q->where('name', 'like', '%DOWN PAYMENT%');
        })->where([
            'sub_ledger' => 'CUSTOMER',
        ])->first();
        $data = [
            'date' => date("Y-m-d H:i:s"),
            'increment_group' => date("Ym"),
            'notes' => "Some notes",
            'details' => [
                [
                    'chart_of_account_id' => $chartOfAccount->id,
                    'chart_of_account_sub_ledger' => $chartOfAccount->sub_ledger,
                    'chart_of_account_type' => [
                        'name' => $chartOfAccount->type->name
                    ],
                    'debit' => $chartOfAccount->position == 'DEBIT' ? 500000 : 0,
                    'credit' => $chartOfAccount->position == 'CREDIT' ? 500000 : 0,
                    'items' => [
                        [
                            'object_id' => 1,
                            'amount' => 500000,
                            'date' => '2023-01-01',
                            'notes' => "Items level notes'"
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(201);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function storeDataItem()
    {
        $item = factory(Item::class)->create();
        $warehouse = factory(Warehouse::class)->create();
        $chartOfAccount = ChartOfAccount::where([
            'sub_ledger' => 'ITEM',
            'position'  => 'DEBIT'
        ])->first();
        $data = [
            'date' => date("Y-m-d H:i:s"),
            'increment_group' => date("Ym"),
            'notes' => "Some notes",
            'details' => [
                [
                    'chart_of_account_id' => $chartOfAccount->id,
                    'chart_of_account_sub_ledger' => $chartOfAccount->sub_ledger,
                    'chart_of_account_type' => [
                        'name' => $chartOfAccount->type->name
                    ],
                    'debit' => 200000,
                    'credit' => 0,
                    'items' => [
                        [
                            'object_id' => $item->id,
                            'warehouse_id' => $warehouse->id,
                            'quantity' => 1,
                            'unit' => 'PCS',
                            'converter' => 1,
                            'price' => 200000,
                            'total' => 200000
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(201);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function storeDataAsset()
    {
        $supplier = factory(Supplier::class)->create();
        $fixedAsset = new FixedAsset();
        $chartOfAccountAccumulation = ChartOfAccount::where('alias', 'like', '%akumulasi penyusutan%')->first();
        $fixedAsset->fill([
            'code' => '12345',
            'name' => 'test',
            'depreciation_method' => FixedAsset::$DEPRECIATION_METHOD_NO_DEPRECIATION,
            'accumulation_chart_of_account_id' => $chartOfAccountAccumulation->id
        ]);
        $fixedAsset->save();
        
        $chartOfAccount = ChartOfAccount::where([
            'sub_ledger' => 'FIXED ASSET',
        ])->first();
        $data = [
            'date' => date("Y-m-d H:i:s"),
            'increment_group' => date("Ym"),
            'notes' => "Some notes",
            'details' => [
                [
                    'chart_of_account_id' => $chartOfAccount->id ?? 1,
                    'chart_of_account_sub_ledger' => $chartOfAccount->sub_ledger ?? 'FIXED ASSET',
                    'chart_of_account_type' => [
                        'name' => $chartOfAccount->type->name
                    ],
                    'debit' => $chartOfAccount->position == 'DEBIT' ? 200000 : 0,
                    'credit' => $chartOfAccount->position == 'CREDIT' ? 200000 : 0,
                    'items' => [
                        [
                            'object_id' => $fixedAsset->id,
                            'supplier_id' => $supplier->id,
                            'location' => 'test',
                            'purchase_date' => date('Y-m-d'),
                            'quantity' => 1,
                            'price' => 200000,
                            'total' => 200000,
                            'accumulation' => 0,
                            'book_value' => 200000
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(201);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function readAllData()
    {
        $response = $this->json('GET', self::$path.'/account', [
            'join' => 'account,cutoff.form',
            'fields' => 'cutoff_accounts.id;cutoff_id;chart_of_account_id;raw:sum(debit) as debit;raw:sum(credit) as credit;cutoff_accounts.created_at;cutoff_accounts.updated_at',
            'group_by' => 'chart_of_account_id',
            'sort_by' => 'account.number',
        ], $this->headers);
        $response->assertStatus(200);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function download()
    {
        $response = $this->json('GET', self::$path.'/account', [
            'isDownload' => true,
            'join' => 'account,cutoff.form',
            'fields' => 'cutoff_accounts.id;cutoff_id;chart_of_account_id;raw:sum(debit) as debit;raw:sum(credit) as credit;cutoff_accounts.created_at;cutoff_accounts.updated_at',
            'group_by' => 'chart_of_account_id',
            'sort_by' => 'account.number',
        ], $this->headers);
        $response->assertStatus(200);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function readSingleData()
    {
        $this->storeDataPayment();
        $cutoff = CutOffAccount::orderBy('id', 'asc')->first();
        $response = $this->json('GET', self::$path.'/account/'.$cutoff->id, [], $this->headers);
        $response->assertStatus(200);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function getTotalCutoff()
    {
        $response = $this->json('GET', self::$path.'/total', [], $this->headers);
        $response->assertStatus(200);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function duplicate()
    {
        $this->storeDataPayment();
        $chartOfAccount = ChartOfAccount::with('type')->where([
            'sub_ledger' => 'CUSTOMER',
            'position'  => 'DEBIT'
        ])->first();
        $data = [
            'date' => date("Y-m-d H:i:s"),
            'increment_group' => date("Ym"),
            'notes' => "Some notes",
            'details' => [
                [
                    'chart_of_account_id' => $chartOfAccount->id,
                    'chart_of_account_sub_ledger' => $chartOfAccount->sub_ledger,
                    'chart_of_account_type' => [
                        'name' => $chartOfAccount->type->name
                    ],
                    'debit' => 500000,
                    'credit' => 0,
                    'items' => [
                        [
                            'object_id' => 1,
                            'amount' => 500000,
                            'date' => '2023-01-01',
                            'notes' => "Items level notes'"
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(422);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function storeRollback()
    {
        $item = factory(Item::class)->create();
        $chartOfAccount = ChartOfAccount::where([
            'sub_ledger' => 'ITEM',
            'position'  => 'DEBIT'
        ])->first();
        $data = [
            'date' => date("Y-m-d H:i:s"),
            'increment_group' => date("Ym"),
            'notes' => "Some notes",
            'details' => [
                [
                    'chart_of_account_id' => $chartOfAccount->id,
                    'chart_of_account_sub_ledger' => $chartOfAccount->sub_ledger,
                    'chart_of_account_type' => [
                        'name' => $chartOfAccount->type->name
                    ],
                    'debit' => 200000,
                    'credit' => 0,
                    'items' => [
                        [
                            'object_id' => $item->id,
                            'warehouse_id' => 12345,
                            'quantity' => 1,
                            'unit' => 'PCS',
                            'converter' => 1,
                            'price' => 200000,
                            'total' => 200000
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->json('POST', self::$path, $data, $this->headers);
        $response->assertStatus(400);
    }
}
