<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Mail\Sales\SalesReturnApprovalRequest;
use App\Model\Form;
use App\Model\SettingJournal;
use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Sales\SalesInvoice\SalesInvoice;
use Illuminate\Support\Facades\Mail;
use App\Helpers\Inventory\InventoryHelper;

class SalesReturnTest extends TestCase
{
  use SalesReturnSetup;

  public static $path = '/api/v1/sales/return';

    /** @test */
    public function unauthorized_no_default_branch_create_sales_return()
    {
      $this->setRole();
      $data = $this->getDummyData();
  
      $this->branchDefault->pivot->is_default = false;
      $this->branchDefault->pivot->save();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
      $response->assertStatus(422)
      ->assertJson([
        'code' => 422,
        'message' => 'please set default branch to create this form'
      ]);
    }
  
    /** @test */
    public function unauthorized_create_sales_return()
    {
      $data = $this->getDummyData();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(500)
        ->assertJson([
          'code' => 0,
          'message' => 'There is no permission named `create sales return` for guard `api`.'
        ]);
    }
    
    /** @test */
    public function invalid_data_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'sales_invoice_id', null);
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJsonFragment([
            'code' => 422,
            'message' => 'The given data was invalid.'
        ]);
    }
    
    /** @test */
    public function duplicate_entry_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $salesReturn->form->number = 'SR22120002';
      $salesReturn->form->save();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
      $response->assertStatus(400)
          ->assertJson([
              'code' => 400,
              'message' => 'Duplicate data entry'
          ]);
    }
    
    /** @test */
    public function error_sales_invoice_done_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
  
      $salesInvoice = SalesInvoice::orderBy('id', 'asc')->first();
      $salesInvoice->form->done = 1;
      $salesInvoice->form->save();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJsonFragment([
          'code' => 422,
          'message' => 'Sales return form already done'
        ]);
    }
    
    /** @test */
    public function error_notes_more_than_255_character_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
  
      $data = data_set($data, 'notes', $this->faker->text(500));
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
          ->assertJson([
              'code' => 422,
              'message' => 'The given data was invalid.',
              'errors' => [
                'notes' => [
                  'The notes may not be greater than 255 characters.'
                ]
              ]
          ]);
    }
    
    /** @test */
    public function whitespaces_trimmed_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
  
      $data = data_set($data, 'notes', ' whitespaces trimmed ');
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(201)
        ->assertJsonFragment([
          'notes' => 'whitespaces trimmed'
        ]);
    }
    
    /** @test */
    public function overquantity_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'items.0.quantity', 100);
      $data = data_set($data, 'items.0.total', 1000000);
      $data = data_set($data, 'sub_total', 1000000);
      $data = data_set($data, 'tax_base', 1000000);
      $data = data_set($data, 'tax', 90909.09090909091);
      $data = data_set($data, 'amount', 1000000);
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'Sales return item can\'t exceed sales invoice qty'
        ]);
    }
    
    /** @test */
    public function invalid_total_item_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'items.0.total', 20000);
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'total for item ' .$data['items'][0]['item_name']. ' should be 30000'
        ]);
    }
    
    /** @test */
    public function invalid_sub_total_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'sub_total', 20000);
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'sub total should be 30000'
        ]);
    }
  
    /** @test */
    public function invalid_tax_base_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'tax_base', 20000);
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'tax base should be 30000'
        ]);
    }
    
    /** @test */
    public function invalid_type_of_tax_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'type_of_tax', 'exclude');
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'type of tax should be same with invoice'
        ]);
    }
    
    /** @test */
    public function invalid_tax_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'tax', 3000);
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'tax should be 2727.2727272727'
        ]);
    }
  
    /** @test */
    public function invalid_amount_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
      $data = data_set($data, 'amount', 40000);
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'amount should be 30000'
        ]);
    }
    
    /** @test */
    public function error_journal_not_found_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
  
      $settingJournal = SettingJournal::where('feature', 'sales')->where('name', 'account receivable')->first();
      $settingJournal->chart_of_account_id = null;
      $settingJournal->save();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'Journal sales account - account receivable not found'
        ]);
    }
    
    /** @test */
    public function check_journal_balance_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
  
      $journal = SalesReturn::checkJournalBalance($salesReturn);
      $this->assertEquals($journal['debit'], $journal['credit']);
    }
    
    /** @test */
    public function success_create_sales_return()
    {
      $this->setRole();
  
      $data = $this->getDummyData();
  
      Mail::fake();
  
      $response = $this->json('POST', self::$path, $data, $this->headers);
  
      Mail::assertQueued(SalesReturnApprovalRequest::class);
  
      $salesReturn = SalesReturn::where('id', $response->json('data.id'))->first();
  
      $this->assertIsObject(
        $salesReturn->salesInvoice(),
        'is sales invoice referenced',
      );
  
      $response->assertStatus(201)
          ->assertJson([
              'data' => [
                  'id' => $response->json('data.id'),
                  'sales_invoice_id' => $data['sales_invoice_id'],
                  'warehouse_id' => $data['warehouse_id'],
                  'customer_id' => $data['customer_id'],
                  'customer_name' => $data['customer_name'],
                  'customer_address' => $data['customer_address'],
                  'customer_phone' => $data['customer_phone'],
                  'tax' => $data['tax'],
                  'amount' => $data['amount'],
                  'form' => [
                      'id' => $salesReturn->form->id,
                      'date' => $response->json('data.form.date'),
                      'number' => 'SR22120001',
                      'edited_number' => $salesReturn->form->edited_number, 
                      'edited_notes' => $salesReturn->form->edited_notes,
                      'notes' => $data['notes'],
                      'created_by' => $salesReturn->form->created_by,
                      'updated_by' => $response->json('data.form.updated_by'),
                      'done' => 0,
                      'increment' => $salesReturn->form->increment,
                      'increment_group' => $salesReturn->form->increment_group,
                      'formable_id' => $response->json('data.id'),
                      'formable_type' => 'SalesReturn',
                      'request_approval_at' => $response->json('data.form.request_approval_at'),
                      'request_approval_to' => $data['request_approval_to'],
                      'approval_by' => $salesReturn->form->approval_by,
                      'approval_at' => $response->json('data.form.approval_at'),
                      'approval_reason' => $salesReturn->form->approval_reason,
                      'approval_status' => 0,
                      'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
                      'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
                      'request_cancellation_at' => $response->json('data.form.request_cancellation_at'),
                      'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
                      'cancellation_approval_at' => $response->json('data.form.cancellation_approval_at'),
                      'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
                      'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
                      'cancellation_status' => $salesReturn->form->cancellation_status,
                      'request_close_to' => $salesReturn->form->request_close_to,
                      'request_close_by' => $salesReturn->form->request_close_by,
                      'request_close_at' => $response->json('data.form.request_close_at'),
                      'request_close_reason' => $salesReturn->form->request_close_reason,
                      'close_approval_at' => $response->json('data.form.close_approval_at'),
                      'close_approval_by' => $salesReturn->form->close_approval_by,
                      'close_status' => $salesReturn->form->close_status,
                  ],
                  'items' => [
                    [
                      'id' => $response->json('data.items.0.id'),
                      'sales_return_id' => $response->json('data.id'),
                      'sales_invoice_item_id' => $data['items'][0]['sales_invoice_item_id'],
                      'item_id' => $data['items'][0]['item_id'],
                      'item_name' => $data['items'][0]['item_name'],
                      'quantity' => $data['items'][0]['quantity'],
                      'quantity_sales' => $data['items'][0]['quantity_sales'],
                      'price' => $data['items'][0]['price'],
                      'discount_percent' => $data['items'][0]['discount_percent'],
                      'discount_value' => $data['items'][0]['discount_value'] .'000000000000000000000000000000',
                      'unit' => $data['items'][0]['unit'],
                      'converter' => $data['items'][0]['converter'],
                      'expiry_date' => $data['items'][0]['expiry_date'],
                      'production_number' => $data['items'][0]['production_number'],
                      'notes' => $data['items'][0]['notes'],
                      'allocation_id' => $data['items'][0]['allocation_id'],
                    ]
                  ]
              ]
          ]);
  
      $this->assertDatabaseHas('forms', [
          'id' => $response->json('data.form.id'),
          'number' => 'SR22120001',
          'notes' => $data['notes'],
          'created_by' => $response->json('data.form.created_by'),
          'updated_by' => $response->json('data.form.updated_by'),
          'approval_status' => 0,
          'done' => 0,
          'formable_id' => $response->json('data.id'),
          'formable_type' => 'SalesReturn',
          'request_approval_to' => $data['request_approval_to'],
      ], 'tenant');
  
      $this->assertDatabaseHas('sales_returns', [
          'id' => $response->json('data.id'),
          'sales_invoice_id' => $data['sales_invoice_id'],
          'customer_id' => $data['customer_id'],
          'customer_name' => $data['customer_name'],
          'customer_address' => $data['customer_address'],
          'customer_phone' => $data['customer_phone'],
          'tax' => $data['tax'],
          'amount' => $data['amount'],
          'warehouse_id' => $data['warehouse_id'],
      ], 'tenant');
  
      $this->assertDatabaseHas('sales_return_items', [
          'sales_return_id' => $response->json('data.id'),
          'sales_invoice_item_id' => $data['items'][0]['sales_invoice_item_id'],
          'item_id' => $data['items'][0]['item_id'],
          'item_name' => $data['items'][0]['item_name'],
          'quantity' => $data['items'][0]['quantity'],
          'quantity_sales' => $data['items'][0]['quantity_sales'],
          'price' => $data['items'][0]['price'],
          'discount_percent' => $data['items'][0]['discount_percent'],
          'discount_value' => $data['items'][0]['discount_value'] .'000000000000000000000000000000',
          'unit' => $data['items'][0]['unit'],
          'converter' => $data['items'][0]['converter'],
          'expiry_date' => $data['items'][0]['expiry_date'],
          'production_number' => $data['items'][0]['production_number'],
          'notes' => $data['items'][0]['notes'],
          'allocation_id' => $data['items'][0]['allocation_id'],
      ], 'tenant');
    }
    
    /** @test */
    public function unauthorized_no_branch_read_all_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->branchDefault->pivot->is_default = false;
      $this->branchDefault->pivot->save();
  
      $data = [
        'join' => 'form,customer,items,item',
        'fields' => 'sales_return.*',
        'sort_by' => '-form.number',
        'group_by' => 'form.id',
        'filter_form' => 'notArchived',
        'filter_like' => '{}',
        'limit' => 10,
        'includes' => 'customer;warehouse;items.item;items.allocation;salesInvoice.form;form.createdBy;form.requestApprovalTo;form.branch',
        'page' => 1
      ];
  
      $response = $this->json('GET', self::$path, $data, $this->headers);
      $response->assertStatus(422)
      ->assertJson([
        'code' => 422,
        'message' => 'please set default branch to read this form'
      ]);
    }
    
    /** @test */
    public function unauthorized_no_branch_read_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->branchDefault->pivot->is_default = false;
      $this->branchDefault->pivot->save();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
  
      $data = [
        'with_archives' => 'true',
        'with_origin' => 'true',
        'remaining_info' => 'true',
        'includes' => 'customer;warehouse;items.item;items.allocation;salesInvoice.form;form.createdBy;form.requestApprovalTo;form.branch'
      ];
  
      $response = $this->json('GET', self::$path . '/' . $salesReturn->id, $data, $this->headers);
      $response->assertStatus(422)
      ->assertJson([
        'code' => 422,
        'message' => 'please set default branch to read this form'
      ]);
    }
    
    /** @test */
    public function unauthorized_read_all_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->unsetUserRole();
  
      $data = [
        'join' => 'form,customer,items,item',
        'fields' => 'sales_return.*',
        'sort_by' => '-form.number',
        'group_by' => 'form.id',
        'filter_form' => 'notArchived',
        'filter_like' => '{}',
        'limit' => 10,
        'includes' => 'customer;warehouse;items.item;items.allocation;salesInvoice.form;form.createdBy;form.requestApprovalTo;form.branch',
        'page' => 1
      ];
  
      $response = $this->json('GET', self::$path, $data, $this->headers);
      $response->assertStatus(500)
        ->assertJson([
          'code' => 0,
          'message' => 'There is no permission named `read sales return` for guard `api`.'
        ]);
    }
    
    /** @test */
    public function unauthorized_read_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->unsetUserRole();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
  
      $data = [
        'with_archives' => 'true',
        'with_origin' => 'true',
        'remaining_info' => 'true',
        'includes' => 'customer;warehouse;items.item;items.allocation;salesInvoice.form;form.createdBy;form.requestApprovalTo;form.branch'
      ];
  
      $response = $this->json('GET', self::$path . '/' . $salesReturn->id, $data, $this->headers);
      $response->assertStatus(500)
        ->assertJson([
          'code' => 0,
          'message' => 'There is no permission named `read sales return` for guard `api`.'
        ]);
    }
    
    /** @test */
    public function success_read_all_sales_return()
    {
        $this->success_create_sales_return();
  
        $data = [
            'join' => 'form,customer,items,item',
            'fields' => 'sales_return.*',
            'sort_by' => '-form.number',
            'group_by' => 'form.id',
            'filter_form' => 'notArchived',
            'filter_like' => '{}',
            'limit' => 10,
            'includes' => 'form;customer;items.item;items.allocation',
            'page' => 1
        ];
  
        $response = $this->json('GET', self::$path, $data, $this->headers);
        $response->assertStatus(200)
          ->assertJsonStructure([
              'data' => [
                [
                  'id',
                  'sales_invoice_id',
                  'customer_id',
                  'warehouse_id',
                  'customer_name',
                  'customer_address',
                  'customer_phone',
                  'tax',
                  'amount',
                  'form' => [
                    'id',
                    'date',
                    'number',
                    'edited_number',
                    'edited_notes',
                    'notes',
                    'created_by',
                    'updated_by',
                    'done',
                    'increment',
                    'increment_group',
                    'formable_id',
                    'formable_type',
                    'request_approval_at',
                    'request_approval_to',
                    'approval_by',
                    'approval_at',
                    'approval_reason',
                    'approval_status',
                    'request_cancellation_to',
                    'request_cancellation_by',
                    'request_cancellation_at',
                    'request_cancellation_reason',
                    'cancellation_approval_at',
                    'cancellation_approval_by',
                    'cancellation_approval_reason',
                    'cancellation_status',
                    'request_close_to',
                    'request_close_by',
                    'request_close_at',
                    'request_close_reason',
                    'close_approval_at',
                    'close_approval_by',
                    'close_status'
                  ],
                  'customer' => [
                    'id',
                    'code',
                    'tax_identification_number',
                    'name',
                    'address',
                    'city',
                    'state',
                    'country',
                    'zip_code',
                    'latitude',
                    'longitude',
                    'phone',
                    'phone_cc',
                    'email',
                    'notes',
                    'credit_limit',
                    'branch_id',
                    'created_by',
                    'updated_by',
                    'archived_by',
                    'pricing_group_id',
                    'label'
                  ],
                  'items' => [
                      [
                        'id',
                        'sales_return_id',
                        'sales_invoice_item_id',
                        'item_id',
                        'item_name',
                        'quantity',
                        'quantity_sales',
                        'price',
                        'discount_percent',
                        'discount_value',
                        'unit',
                        'converter',
                        'expiry_date',
                        'production_number',
                        'notes',
                        'allocation_id',
                        'item' => [
                          'id',
                          'chart_of_account_id',
                          'code',
                          'barcode',
                          'name',
                          'size',
                          'color',
                          'weight',
                          'notes',
                          'taxable',
                          'require_production_number',
                          'require_expiry_date',
                          'stock',
                          'stock_reminder',
                          'unit_default',
                          'unit_default_purchase',
                          'unit_default_sales',
                          'label'
                        ]
                      ]
                  ]
                ]                
              ],
              'links' => [
                'first',
                'last',
                'prev',
                'next',
              ],
              'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
              ]
          ]);
        $this->assertGreaterThan(0, count($response->json('data')));
    }
    
    /** @test */
    public function read_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $salesReturnItem = $salesReturn->items[0];
      $salesReturnForm = $salesReturn->form;
  
      $data = [
          'with_archives' => 'true',
          'with_origin' => 'true',
          'remaining_info' => 'true',
          'includes' => 'customer;items.item;items.allocation;salesInvoice.form;form.createdBy;form.requestApprovalTo;form.branch'
      ];
  
      $response = $this->json('GET', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(200)
        ->assertJson([
          'data' => [
            'id' => $salesReturn->id,
            'sales_invoice_id' => $salesReturn->sales_invoice_id,
            'warehouse_id' => $salesReturn->warehouse_id,
            'customer_id' => $salesReturn->customer_id,
            'customer_name' => $salesReturn->customer_name,
            'customer_address' => $salesReturn->customer_address,
            'customer_phone' => $salesReturn->customer_phone,
            'tax' => $salesReturn->tax,
            'amount' => $salesReturn->amount,
            'archives' => [],
            'form' => [
              'id' => $salesReturn->form->id,
              'date' => $response->json('data.form.date'),
              'number' => $salesReturnForm->number,
              'edited_number' => $salesReturnForm->edited_number, 
              'edited_notes' => $salesReturnForm->edited_notes,
              'notes' => $salesReturnForm->notes,
              'created_by' => $salesReturnForm->created_by,
              'updated_by' => $response->json('data.form.updated_by'),
              'done' => $salesReturnForm->done,
              'increment' => $salesReturnForm->increment,
              'increment_group' => $salesReturnForm->increment_group,
              'formable_id' => $salesReturnForm->formable_id,
              'formable_type' => $salesReturnForm->formable_type,
              'request_approval_at' => $response->json('data.form.request_approval_at'),
              'request_approval_to' => $salesReturnForm->request_approval_to,
              'approval_by' => $salesReturnForm->approval_by,
              'approval_at' => $response->json('data.form.approval_at'),
              'approval_reason' => $salesReturnForm->approval_reason,
              'approval_status' => $salesReturnForm->approval_status,
              'request_cancellation_to' => $salesReturnForm->request_cancellation_to,
              'request_cancellation_by' => $salesReturnForm->request_cancellation_by,
              'request_cancellation_at' => $response->json('data.form.request_cancellation_at'),
              'request_cancellation_reason' => $salesReturnForm->request_cancellation_reason,
              'cancellation_approval_at' => $response->json('data.form.cancellation_approval_at'),
              'cancellation_approval_by' => $salesReturnForm->cancellation_approval_by,
              'cancellation_approval_reason' => $salesReturnForm->cancellation_approval_reason,
              'cancellation_status' => $salesReturnForm->cancellation_status,
              'request_close_to' => $salesReturnForm->request_close_to,
              'request_close_by' => $salesReturnForm->request_close_by,
              'request_close_at' => $response->json('data.form.request_close_at'),
              'request_close_reason' => $salesReturnForm->request_close_reason,
              'close_approval_at' => $response->json('data.form.close_approval_at'),
              'close_approval_by' => $salesReturnForm->close_approval_by,
              'close_status' => $salesReturnForm->close_status,
              'created_by' => [
                'id' => $salesReturnForm->createdBy->id,
                'name' => $salesReturnForm->createdBy->name,
                'first_name' => $salesReturnForm->createdBy->first_name,
                'last_name' => $salesReturnForm->createdBy->last_name,
                'address' => $salesReturnForm->createdBy->address,
                'phone' => $salesReturnForm->createdBy->phone,
                'email' => $salesReturnForm->createdBy->email,
                'branch_id' => $salesReturnForm->createdBy->branch_id,
                'warehouse_id' => $salesReturnForm->createdBy->warehouse_id,
                'full_name' => $salesReturnForm->createdBy->full_name
              ],
              'request_approval_to' => [
                'id' => $salesReturnForm->requestApprovalTo->id,
                'name' => $salesReturnForm->requestApprovalTo->name,
                'first_name' => $salesReturnForm->requestApprovalTo->first_name,
                'last_name' => $salesReturnForm->requestApprovalTo->last_name,
                'address' => $salesReturnForm->requestApprovalTo->address,
                'phone' => $salesReturnForm->requestApprovalTo->phone,
                'email' => $salesReturnForm->requestApprovalTo->email,
                'branch_id' => $salesReturnForm->requestApprovalTo->branch_id,
                'warehouse_id' => $salesReturnForm->requestApprovalTo->warehouse_id,
                'full_name' => $salesReturnForm->requestApprovalTo->full_name
              ],
              'branch' => [
                'id' => $salesReturnForm->branch->id,
                'name' => $salesReturnForm->branch->name,
                'address' => $salesReturnForm->branch->address,
                'phone' => $salesReturnForm->branch->phone,
                'archived_at' => $salesReturnForm->branch->archived_at,
              ]
            ],
            'items' => [
              [
                'id' => $salesReturnItem->id,
                'sales_return_id' => $salesReturnItem->sales_return_id,
                'sales_invoice_item_id' => $salesReturnItem->sales_invoice_item_id,
                'item_id' => $salesReturnItem->item_id,
                'item_name' => $salesReturnItem->item_name,
                'quantity' => $salesReturnItem->quantity,
                'quantity_sales' => $salesReturnItem->quantity_sales,
                'price' => $salesReturnItem->price,
                'discount_percent' => $salesReturnItem->discount_percent,
                'discount_value' => $salesReturnItem->discount_value,
                'unit' => $salesReturnItem->unit,
                'converter' => $salesReturnItem->converter,
                'expiry_date' => $salesReturnItem->expiry_date,
                'production_number' => $salesReturnItem->production_number,
                'notes' => $salesReturnItem->notes,
                'allocation_id' => $salesReturnItem->allocation_id,
                'item' => [
                  'id' => $salesReturnItem->item->id,
                  'chart_of_account_id' => $salesReturnItem->item->chart_of_account_id,
                  'code' => $salesReturnItem->item->code,
                  'barcode' => $salesReturnItem->item->barcode,
                  'name' => $salesReturnItem->item->name,
                  'size' => $salesReturnItem->item->size,
                  'color' => $salesReturnItem->item->color,
                  'weight' => $salesReturnItem->item->weight,
                  'notes' => $salesReturnItem->item->notes,
                  'taxable' => $salesReturnItem->item->taxable,
                  'require_production_number' => $salesReturnItem->item->require_production_number,
                  'require_expiry_date' => $salesReturnItem->item->require_expiry_date,
                  'stock' => $salesReturnItem->item->stock,
                  'stock_reminder' => $salesReturnItem->item->stock_reminder,
                  'unit_default' => $salesReturnItem->item->unit_default,
                  'unit_default_purchase' => $salesReturnItem->item->unit_default_purchase,
                  'unit_default_sales' => $salesReturnItem->item->unit_default_sales,
                  'label' => $salesReturnItem->item->label,
                ],
                'allocation' => null
              ]
            ],
            'sales_invoice' => [
              'id' => $salesReturn->salesInvoice->id,
              'customer_id' => $salesReturn->salesInvoice->customer_id,
              'customer_name' => $salesReturn->salesInvoice->customer_name,
              'customer_address' => $salesReturn->salesInvoice->customer_address,
              'customer_phone' => $salesReturn->salesInvoice->customer_phone,
              'discount_percent' => $salesReturn->salesInvoice->discount_percent,
              'discount_value' => $salesReturn->salesInvoice->discount_value,
              'type_of_tax' => $salesReturn->salesInvoice->type_of_tax,
              'tax' => $salesReturn->salesInvoice->tax,
              'amount' => $salesReturn->salesInvoice->amount,
              'remaining' => $salesReturn->salesInvoice->remaining,
              'form' => [
                'id' => $salesReturn->salesInvoice->form->id,
                'date' => $response->json('data.sales_invoice.form.date'),
                'number' => $salesReturn->salesInvoice->form->number,
                'edited_number' => $salesReturn->salesInvoice->form->edited_number, 
                'edited_notes' => $salesReturn->salesInvoice->form->edited_notes,
                'notes' => $salesReturn->salesInvoice->form->notes,
                'created_by' => $salesReturn->salesInvoice->form->created_by,
                'updated_by' => $response->json('data.sales_invoice.form.updated_by'),
                'done' => $salesReturn->salesInvoice->form->done,
                'increment' => $salesReturn->salesInvoice->form->increment,
                'increment_group' => $salesReturn->salesInvoice->form->increment_group,
                'formable_id' => $salesReturn->salesInvoice->form->formable_id,
                'formable_type' => $salesReturn->salesInvoice->form->formable_type,
                'request_approval_at' => $response->json('data.sales_invoice.form.request_approval_at'),
                'request_approval_to' => $salesReturn->salesInvoice->form->request_approval_to,
                'approval_by' => $salesReturn->salesInvoice->form->approval_by,
                'approval_at' => $response->json('data.sales_invoice.form.approval_at'),
                'approval_reason' => $salesReturn->salesInvoice->form->approval_reason,
                'approval_status' => $salesReturn->salesInvoice->form->approval_status,
                'request_cancellation_to' => $salesReturn->salesInvoice->form->request_cancellation_to,
                'request_cancellation_by' => $salesReturn->salesInvoice->form->request_cancellation_by,
                'request_cancellation_at' => $response->json('data.sales_invoice.form.request_cancellation_at'),
                'request_cancellation_reason' => $salesReturn->salesInvoice->form->request_cancellation_reason,
                'cancellation_approval_at' => $response->json('data.sales_invoice.form.cancellation_approval_at'),
                'cancellation_approval_by' => $salesReturn->salesInvoice->form->cancellation_approval_by,
                'cancellation_approval_reason' => $salesReturn->salesInvoice->form->cancellation_approval_reason,
                'cancellation_status' => $salesReturn->salesInvoice->form->cancellation_status,
                'request_close_to' => $salesReturn->salesInvoice->form->request_close_to,
                'request_close_by' => $salesReturn->salesInvoice->form->request_close_by,
                'request_close_at' => $response->json('data.sales_invoice.form.request_close_at'),
                'request_close_reason' => $salesReturn->salesInvoice->form->request_close_reason,
                'close_approval_at' => $response->json('data.sales_invoice.form.close_approval_at'),
                'close_approval_by' => $salesReturn->salesInvoice->form->close_approval_by,
                'close_status' => $salesReturn->salesInvoice->form->close_status,
              ]
            ]
          ]
        ]);
    }
    
    /** @test */
    public function unauthorized_no_default_branch_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->branchDefault->pivot->is_default = false;
      $this->branchDefault->pivot->save();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data = $this->getDummyData($salesReturn);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'please set default branch to update this form'
        ]);
    }
    
    /** @test */
    public function referenced_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
  
      $this->createPaymentCollection($salesReturn);
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'form referenced by payment collection'
        ]);
    }
    
    /** @test */
    public function unauthorized_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->unsetUserRole();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data = $this->getDummyData($salesReturn);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(500)
        ->assertJson([
          'code' => 0,
          'message' => 'There is no permission named `update sales return` for guard `api`.'
        ]);
    }
    
    /** @test */
    public function invalid_data_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'sales_invoice_id', null);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'The given data was invalid.'
        ]);
    }
    
    /** @test */
    public function error_form_done_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $salesReturn->form->done = 1;
      $salesReturn->form->save();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'form already done'
        ]);
    }
    
    /** @test */
    public function error_notes_more_than_255_character_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'notes', $this->faker->text(500));
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'The given data was invalid.',
          'errors' => [
            'notes' => [
              'The notes may not be greater than 255 characters.'
            ]
          ]
        ]);
    }
    
    /** @test */
    public function whitespaces_trimmed_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'notes', ' whitespaces trimmed ');
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(201)
        ->assertJsonFragment([
          'notes' => 'whitespaces trimmed'
        ]);
    }
    
    /** @test */
    public function overquantity_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'items.0.quantity', 100);
      $data = data_set($data, 'items.0.total', 1000000);
      $data = data_set($data, 'sub_total', 1000000);
      $data = data_set($data, 'tax_base', 1000000);
      $data = data_set($data, 'tax', 90909.09090909091);
      $data = data_set($data, 'amount', 1000000);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'Sales return item can\'t exceed sales invoice qty'
        ]);
    }
    
    /** @test */
    public function invalid_total_item_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'items.0.total', 20000);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'total for item ' .$data['items'][0]['item_name']. ' should be 30000'
        ]);
    }
    
    /** @test */
    public function invalid_sub_total_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'sub_total', 20000);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'sub total should be 30000'
        ]);
    }
  
    /** @test */
    public function invalid_tax_base_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'tax_base', 20000);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'tax base should be 30000'
        ]);
    }
  
    /** @test */
    public function invalid_type_of_tax_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'type_of_tax', 'exclude');
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'type of tax should be same with invoice'
        ]);
    }
  
    /** @test */
    public function invalid_tax_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'tax', 3000);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'tax should be 2727.2727272727'
        ]);
    }
  
    /** @test */
    public function invalid_amount_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      $data = data_set($data, 'amount', 40000);
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'amount should be 30000'
        ]);
    }
    
    /** @test */
    public function error_journal_not_found_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      
      $settingJournal = SettingJournal::where('feature', 'sales')->where('name', 'account receivable')->first();
      $settingJournal->chart_of_account_id = null;
      $settingJournal->save();
  
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'Journal sales account - account receivable not found'
        ]);
    }
  
    /** @test */
    public function check_journal_balance_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($salesReturn);
      $data = data_set($data, 'id', $salesReturn->id, false);
      
      $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $salesReturn = SalesReturn::where('id', $response->json('data.id'))->first();
      $journal = SalesReturn::checkJournalBalance($salesReturn);
      $this->assertEquals($journal['debit'], $journal['credit']);
    }
    
    /** @test */
    public function success_update_sales_return()
    {
      $this->success_create_sales_return();
  
      $oldSalesReturn = SalesReturn::orderBy('id', 'asc')->first();
      
      $data = $this->getDummyData($oldSalesReturn);
      $data = data_set($data, 'id', $oldSalesReturn->id, false);
  
      Mail::fake();
  
      $response = $this->json('PATCH', self::$path . '/' . $oldSalesReturn->id, $data, $this->headers);
  
      Mail::assertQueued(SalesReturnApprovalRequest::class);
  
      $salesReturn = SalesReturn::where('id', $response->json('data.id'))->first();
  
      $this->assertIsObject(
        $salesReturn->salesInvoice(),
        'is sales invoice referenced',
      );
  
      $response->assertStatus(201)
          ->assertJson([
            'data' => [
              'id' => $response->json('data.id'),
              'sales_invoice_id' => $data['sales_invoice_id'],
              'warehouse_id' => $data['warehouse_id'],
              'customer_id' => $data['customer_id'],
              'customer_name' => $data['customer_name'],
              'customer_address' => $data['customer_address'],
              'customer_phone' => $data['customer_phone'],
              'tax' => $data['tax'],
              'amount' => $data['amount'],
              'form' => [
                  'id' => $salesReturn->form->id,
                  'date' => $response->json('data.form.date'),
                  'number' => 'SR22120001',
                  'edited_number' => $salesReturn->form->edited_number, 
                  'edited_notes' => $salesReturn->form->edited_notes,
                  'notes' => $data['notes'],
                  'created_by' => $salesReturn->form->created_by,
                  'updated_by' => $response->json('data.form.updated_by'),
                  'done' => 0,
                  'increment' => $salesReturn->form->increment,
                  'increment_group' => $salesReturn->form->increment_group,
                  'formable_id' => $response->json('data.id'),
                  'formable_type' => 'SalesReturn',
                  'request_approval_at' => $response->json('data.form.request_approval_at'),
                  'request_approval_to' => $data['request_approval_to'],
                  'approval_by' => $salesReturn->form->approval_by,
                  'approval_at' => $response->json('data.form.approval_at'),
                  'approval_reason' => $salesReturn->form->approval_reason,
                  'approval_status' => 0,
                  'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
                  'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
                  'request_cancellation_at' => $response->json('data.form.request_cancellation_at'),
                  'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
                  'cancellation_approval_at' => $response->json('data.form.cancellation_approval_at'),
                  'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
                  'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
                  'cancellation_status' => $salesReturn->form->cancellation_status,
                  'request_close_to' => $salesReturn->form->request_close_to,
                  'request_close_by' => $salesReturn->form->request_close_by,
                  'request_close_at' => $response->json('data.form.request_close_at'),
                  'request_close_reason' => $salesReturn->form->request_close_reason,
                  'close_approval_at' => $response->json('data.form.close_approval_at'),
                  'close_approval_by' => $salesReturn->form->close_approval_by,
                  'close_status' => $salesReturn->form->close_status,
              ],
              'items' => [
                [
                  'id' => $response->json('data.items.0.id'),
                  'sales_return_id' => $response->json('data.id'),
                  'sales_invoice_item_id' => $data['items'][0]['sales_invoice_item_id'],
                  'item_id' => $data['items'][0]['item_id'],
                  'item_name' => $data['items'][0]['item_name'],
                  'quantity' => $data['items'][0]['quantity'],
                  'quantity_sales' => $data['items'][0]['quantity_sales'],
                  'price' => $data['items'][0]['price'],
                  'discount_percent' => $data['items'][0]['discount_percent'],
                  'discount_value' => $data['items'][0]['discount_value'] .'000000000000000000000000000000',
                  'unit' => $data['items'][0]['unit'],
                  'converter' => $data['items'][0]['converter'],
                  'expiry_date' => $data['items'][0]['expiry_date'],
                  'production_number' => $data['items'][0]['production_number'],
                  'notes' => $data['items'][0]['notes'],
                  'allocation_id' => $data['items'][0]['allocation_id'],
                ]
              ]
            ]
          ]);
  
      $this->assertDatabaseHas('forms', [
        'id' => $oldSalesReturn->form->id,
        'edited_number' => $oldSalesReturn->form->edited_number,
        'formable_id' => $oldSalesReturn->id,
        'formable_type' => 'SalesReturn',
      ], 'tenant');
      $this->assertDatabaseHas('user_activities', [
          'number' => $response->json('data.form.number'),
          'table_id' => $response->json('data.id'),
          'table_type' => 'SalesReturn',
          'activity' => 'Update - 1'
      ], 'tenant');
  
      $this->assertDatabaseHas('forms', [
          'id' => $response->json('data.form.id'),
          'number' => $oldSalesReturn->form->edited_number,
          'notes' => $data['notes'],
          'created_by' => $response->json('data.form.created_by'),
          'updated_by' => $response->json('data.form.updated_by'),
          'approval_status' => 0,
          'done' => 0,
          'formable_id' => $response->json('data.id'),
          'formable_type' => 'SalesReturn',
          'request_approval_to' => $data['request_approval_to'],
      ], 'tenant');
  
      $this->assertDatabaseHas('sales_returns', [
          'id' => $response->json('data.id'),
          'sales_invoice_id' => $data['sales_invoice_id'],
          'customer_id' => $data['customer_id'],
          'customer_name' => $data['customer_name'],
          'customer_address' => $data['customer_address'],
          'customer_phone' => $data['customer_phone'],
          'tax' => $data['tax'],
          'amount' => $data['amount'],
          'warehouse_id' => $data['warehouse_id'],
      ], 'tenant');
  
      $this->assertDatabaseHas('sales_return_items', [
          'sales_return_id' => $response->json('data.id'),
          'sales_invoice_item_id' => $data['items'][0]['sales_invoice_item_id'],
          'item_id' => $data['items'][0]['item_id'],
          'item_name' => $data['items'][0]['item_name'],
          'quantity' => $data['items'][0]['quantity'],
          'quantity_sales' => $data['items'][0]['quantity_sales'],
          'price' => $data['items'][0]['price'],
          'discount_percent' => $data['items'][0]['discount_percent'],
          'discount_value' => $data['items'][0]['discount_value'] .'000000000000000000000000000000',
          'unit' => $data['items'][0]['unit'],
          'converter' => $data['items'][0]['converter'],
          'expiry_date' => $data['items'][0]['expiry_date'],
          'production_number' => $data['items'][0]['production_number'],
          'notes' => $data['items'][0]['notes'],
          'allocation_id' => $data['items'][0]['allocation_id'],
      ], 'tenant');
    }
    
    /** @test */
    public function unauthorized_different_default_branch_delete_sales_return()
    {
      $this->success_create_sales_return();
  
      $branch = $this->createBranch();
      $this->branchDefault->pivot->branch_id = $branch->id;
      $this->branchDefault->pivot->is_default = true;
      $this->branchDefault->pivot->save();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);
  
      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'please set default branch to delete this form'
        ]);
    }
    
    /** @test */
    public function unauthorized_no_warehouse_default_branch_delete_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->removeUserWarehouse();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);
  
      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'please set default warehouse to delete this form'
        ]);
    }
    
    /** @test */
    public function unauthorized_delete_sales_return()
    {
      $this->success_create_sales_return();
  
      $this->unsetUserRole();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);
  
      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(500)
        ->assertJson([
          'code' => 0,
          'message' => 'There is no permission named `delete sales return` for guard `api`.'
        ]);
    }
    /** @test */
    public function error_form_done_delete_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $salesReturn->form->done = 1;
      $salesReturn->form->save();
  
      $data['reason'] = $this->faker->text(200);
  
      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'form already done'
        ]);
    }
  
    /** @test */
    public function referenced_delete_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $this->createPaymentCollection($salesReturn);
  
      $data['reason'] = $this->faker->text(200);
  
      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'form referenced by payment collection'
        ]);
    }
    
    /** @test */
    public function error_empty_reason_delete_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
  
      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, [], $this->headers);
  
      $response->assertStatus(422)
        ->assertJson([
          'code' => 422,
          'message' => 'The given data was invalid.',
          'errors' => [
            'reason' => [
              'The reason field is required.'
            ]
          ]
        ]);
    }
    
    /** @test */
    public function success_delete_sales_return()
    {
      $this->success_create_sales_return();
  
      $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
      $data['reason'] = $this->faker->text(200);
  
      Mail::fake();
  
      $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
  
      $response->assertStatus(204);
      
      Mail::assertQueued(SalesReturnApprovalRequest::class);
  
      $this->assertDatabaseHas('forms', [
        'number' => $salesReturn->form->number,
        'request_cancellation_reason' => $data['reason'],
        'cancellation_status' => 0,
      ], 'tenant');
    }
}