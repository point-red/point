<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Token;
use App\User;
use App\Helpers\Inventory\InventoryHelper;

class SalesReturnApprovalByEmailTest extends TestCase
{
    use SalesReturnSetup;

    public static $path = '/api/v1/sales/return';
    public static $paycolPath = '/api/v1/sales/payment-collection';

    private function findOrCreateToken($tenantUser)
    {
        $approverToken = Token::where('user_id', $tenantUser->id)->first();
        if (!$approverToken) {
            $approverToken = new Token();
            $approverToken->user_id = $tenantUser->id;
            $approverToken->token = md5($tenantUser->email.''.now());
            $approverToken->save();
        }

        return $approverToken;
    }
    private function changeActingAs($tenantUser, $salesReturn)
    {
        $tenantUser->branches()->syncWithoutDetaching($salesReturn->form->branch_id);
        foreach ($tenantUser->branches as $branch) {
            $branch->pivot->is_default = true;
            $branch->pivot->save();
        }
        $tenantUser->warehouses()->syncWithoutDetaching($salesReturn->warehouse_id);
        foreach ($tenantUser->warehouses as $warehouse) {
            $warehouse->pivot->is_default = true;
            $warehouse->pivot->save();
        }
        $user = new User();
        $user->id = $tenantUser->id;
        $user->name = $tenantUser->name;
        $user->email = $tenantUser->email;
        $user->save();
        $this->actingAs($user, 'api');
    }

    public function create_sales_return()
    {
        $this->setRole();

        $data = $this->getDummyData();

        $this->json('POST', self::$path, $data, $this->headers);
    }

    public function delete_sales_return()
    {
        $this->create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
    }

    public function approve_sales_return()
    {
        $this->create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $this->json('POST', self::$path . '/' . $salesReturn->id . '/approve', [], $this->headers);
    }

    public function delete_approved_sales_return()
    {
        $this->approve_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);
    }

    /** @test */
    public function error_already_approved_approve_by_email_sales_return()
    {
        $this->create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $salesReturn->form->approval_status = 1;
        $salesReturn->form->save();

        $approver = $salesReturn->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $salesReturn);

        $data = [
            'action' => 'approve',
            'approver_id' => $salesReturn->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesReturn',
            'ids' => [
                ['id' => $salesReturn->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data , $this->headers);

        $response->assertStatus(422)
        ->assertJson([
            'code' => 422,
            'message' => 'form '.$salesReturn->form->number.' already approved'
        ]);
    }

    /** @test */
    public function unauthorized_approve_by_email_sales_return()
    {
        $this->create_sales_return();

        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales return` for guard `api`."
            ]);
    }
	
	/** @test */
    public function success_approve_by_email_sales_return()
    {
        $this->create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $approver = $salesReturn->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $salesReturn);

        $data = [
            'action' => 'approve',
            'approver_id' => $salesReturn->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesReturn',
            'ids' => [
                ['id' => $salesReturn->id]
            ],
            'crud-type' => 'delete'
        ];

        $salesReturnItem = $salesReturn->items[0];

        $stock = InventoryHelper::getCurrentStock($salesReturnItem->item, $salesReturn->form->date, $salesReturn->warehouse, [
            'expiry_date' => $salesReturnItem->item->expiry_date,
            'production_number' => $salesReturnItem->item->production_number,
        ]);

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);
        $salesReturn = SalesReturn::where('id', $salesReturn->id)->first();
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $salesReturn->id,
                        'sales_invoice_id' => $salesReturn->sales_invoice_id,
                        'warehouse_id' => $salesReturn->warehouse_id,
                        'customer_id' => $salesReturn->customer_id,
                        'customer_name' => $salesReturn->customer_name,
                        'customer_address' => $salesReturn->customer_address,
                        'customer_phone' => $salesReturn->customer_phone,
                        'tax' => $salesReturn->tax,
                        'amount' => $salesReturn->amount,
                        'form' => [
                            'id' => $salesReturn->form->id,
                            'date' => $response->json('data.0.form.date'),
                            'number' => $salesReturn->form->number,
                            'edited_number' => $salesReturn->form->edited_number, 
                            'edited_notes' => $salesReturn->form->edited_notes,
                            'notes' => $salesReturn->form->notes,
                            'created_by' => $salesReturn->form->created_by,
                            'updated_by' => $response->json('data.0.form.updated_by'),
                            'done' => $salesReturn->form->done,
                            'increment' => $salesReturn->form->increment,
                            'increment_group' => $salesReturn->form->increment_group,
                            'formable_id' => $salesReturn->form->formable_id,
                            'formable_type' => $salesReturn->form->formable_type,
                            'request_approval_at' => $response->json('data.0.form.request_approval_at'),
                            'request_approval_to' => $salesReturn->form->request_approval_to,
                            'approval_by' => $salesReturn->form->approval_by,
                            'approval_at' => $response->json('data.0.form.approval_at'),
                            'approval_reason' => $salesReturn->form->approval_reason,
                            'approval_status' => 1,
                            'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
                            'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
                            'request_cancellation_at' => $response->json('data.0.form.request_cancellation_at'),
                            'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
                            'cancellation_approval_at' => $response->json('data.0.form.cancellation_approval_at'),
                            'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
                            'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
                            'cancellation_status' => $salesReturn->form->cancellation_status,
                            'request_close_to' => $salesReturn->form->request_close_to,
                            'request_close_by' => $salesReturn->form->request_close_by,
                            'request_close_at' => $response->json('data.0.form.request_close_at'),
                            'request_close_reason' => $salesReturn->form->request_close_reason,
                            'close_approval_at' => $response->json('data.0.form.close_approval_at'),
                            'close_approval_by' => $salesReturn->form->close_approval_by,
                            'close_status' => $salesReturn->form->close_status,
                        ]
                    ]
                ]
            ]);
    
        $subTotal = $response->json('data.0.amount') - $response->json('data.0.tax');
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.0.form.id'),
            'number' => $response->json('data.0.form.number'),
            'approval_status' => 1
        ], 'tenant');
    
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.0.form.number'),
            'table_id' => $response->json('data.0.id'),
            'table_type' => 'SalesReturn',
            'activity' => 'Approved By Email'
        ], 'tenant');
    
        $this->assertDatabaseHas('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->arCoa->id,
            'credit' => $response->json('data.0.amount').'.000000000000000000000000000000'
        ], 'tenant');
        $this->assertDatabaseHas('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->salesIncomeCoa->id,
            'debit' => $subTotal.'.000000000000000000000000000000'
        ], 'tenant');
        $this->assertDatabaseHas('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->taxCoa->id,
            'debit' => $response->json('data.0.tax').'.000000000000000000000000000000'
        ], 'tenant');
    
        $stockNew = InventoryHelper::getCurrentStock($salesReturnItem->item, $salesReturn->form->date, $salesReturn->warehouse, [
            'expiry_date' => $salesReturnItem->item->expiry_date,
            'production_number' => $salesReturnItem->item->production_number,
        ]);
        $this->assertEquals($stockNew, ($stock + $salesReturnItem->quantity));
    
        $referenced = $this->json('GET', self::$paycolPath. '/'.$salesReturn->customer_id.'/references', [], $this->headers);
        $referenced->assertStatus(200)
            ->assertJson([
                'data' => [
                    'salesReturn' => [
                        [ 'number' => $salesReturn->form->number ]
                    ]
                ]          
            ]);
    }

    /** @test */
    public function success_approve_delete_by_email_sales_return()
    {
        $this->delete_approved_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $salesReturnItem = $salesReturn->items[0];

        $approver = $salesReturn->form->requestCancellationTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $salesReturn);

        $data = [
            'action' => 'approve',
            'approver_id' => $salesReturn->form->request_cancellation_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesReturn',
            'ids' => [
                ['id' => $salesReturn->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);

        $salesReturn = SalesReturn::where('id', $salesReturn->id)->first();
        $salesReturnItem = $salesReturn->items[0];
        $response->assertStatus(200)
            ->assertJson([
            'data' => [
                [
                    'id' => $salesReturn->id,
                    'sales_invoice_id' => $salesReturn->sales_invoice_id,
                    'warehouse_id' => $salesReturn->warehouse_id,
                    'customer_id' => $salesReturn->customer_id,
                    'customer_name' => $salesReturn->customer_name,
                    'customer_address' => $salesReturn->customer_address,
                    'customer_phone' => $salesReturn->customer_phone,
                    'tax' => $salesReturn->tax,
                    'amount' => $salesReturn->amount,
                    'form' => [
                        'id' => $salesReturn->form->id,
                        'date' => $response->json('data.0.form.date'),
                        'number' => $salesReturn->form->number,
                        'edited_number' => $salesReturn->form->edited_number, 
                        'edited_notes' => $salesReturn->form->edited_notes,
                        'notes' => $salesReturn->form->notes,
                        'created_by' => $salesReturn->form->created_by,
                        'updated_by' => $response->json('data.0.form.updated_by'),
                        'done' => $salesReturn->form->done,
                        'increment' => $salesReturn->form->increment,
                        'increment_group' => $salesReturn->form->increment_group,
                        'formable_id' => $salesReturn->form->formable_id,
                        'formable_type' => $salesReturn->form->formable_type,
                        'request_approval_at' => $response->json('data.0.form.request_approval_at'),
                        'request_approval_to' => $salesReturn->form->request_approval_to,
                        'approval_by' => $salesReturn->form->approval_by,
                        'approval_at' => $response->json('data.0.form.approval_at'),
                        'approval_reason' => $salesReturn->form->approval_reason,
                        'approval_status' => $salesReturn->form->approval_status,
                        'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
                        'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
                        'request_cancellation_at' => $response->json('data.0.form.request_cancellation_at'),
                        'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
                        'cancellation_approval_at' => $response->json('data.0.form.cancellation_approval_at'),
                        'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
                        'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
                        'cancellation_status' => 1,
                        'request_close_to' => $salesReturn->form->request_close_to,
                        'request_close_by' => $salesReturn->form->request_close_by,
                        'request_close_at' => $response->json('data.0.form.request_close_at'),
                        'request_close_reason' => $salesReturn->form->request_close_reason,
                        'close_approval_at' => $response->json('data.0.form.close_approval_at'),
                        'close_approval_by' => $salesReturn->form->close_approval_by,
                        'close_status' => $salesReturn->form->close_status,
                ]              
              ]
            ]
          ]);

        $subTotal = $response->json('data.0.amount') - $response->json('data.0.tax');
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.0.form.id'),
            'number' => $response->json('data.0.form.number'),
            'cancellation_status' => 1
        ], 'tenant');

        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.0.form.number'),
            'table_id' => $response->json('data.0.id'),
            'table_type' => 'SalesReturn',
            'activity' => 'Cancellation Approved by Email'
        ], 'tenant');

        $this->assertDatabaseMissing('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->arCoa->id,
            'credit' => $response->json('data.0.amount').'.000000000000000000000000000000'
        ], 'tenant');
        $this->assertDatabaseMissing('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->salesIncomeCoa->id,
            'debit' => $subTotal.'.000000000000000000000000000000'
        ], 'tenant');
        $this->assertDatabaseMissing('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->taxCoa->id,
            'debit' => $response->json('data.tax').'.000000000000000000000000000000'
        ], 'tenant');
    }
	
	/** @test */
    public function unauthorized_reject_by_email_sales_return()
    {
        $this->create_sales_return();

        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales return` for guard `api`."
            ]);
    }

    /** @test */
    public function success_reject_sales_return()
    {
        $this->create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $approver = $salesReturn->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $salesReturn);

        $data = [
            'action' => 'reject',
            'approver_id' => $salesReturn->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesReturn',
            'ids' => [
                ['id' => $salesReturn->id]
            ],
            'crud-type' => 'delete',
            'reason' => $this->faker->text(200)
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);
        $salesReturn = SalesReturn::where('id', $salesReturn->id)->first();
        $response->assertStatus(200)
        ->assertJson([
            'data' => [
                [
                    'id' => $salesReturn->id,
                    'sales_invoice_id' => $salesReturn->sales_invoice_id,
                    'warehouse_id' => $salesReturn->warehouse_id,
                    'customer_id' => $salesReturn->customer_id,
                    'customer_name' => $salesReturn->customer_name,
                    'customer_address' => $salesReturn->customer_address,
                    'customer_phone' => $salesReturn->customer_phone,
                    'tax' => $salesReturn->tax,
                    'amount' => $salesReturn->amount,
                    'form' => [
                      'id' => $salesReturn->form->id,
                      'date' => $response->json('data.0.form.date'),
                      'number' => $salesReturn->form->number,
                      'edited_number' => $salesReturn->form->edited_number, 
                      'edited_notes' => $salesReturn->form->edited_notes,
                      'notes' => $salesReturn->form->notes,
                      'created_by' => $salesReturn->form->created_by,
                      'updated_by' => $response->json('data.0.form.updated_by'),
                      'done' => $salesReturn->form->done,
                      'increment' => $salesReturn->form->increment,
                      'increment_group' => $salesReturn->form->increment_group,
                      'formable_id' => $salesReturn->form->formable_id,
                      'formable_type' => $salesReturn->form->formable_type,
                      'request_approval_at' => $response->json('data.0.form.request_approval_at'),
                      'request_approval_to' => $salesReturn->form->request_approval_to,
                      'approval_by' => $salesReturn->form->approval_by,
                      'approval_at' => $response->json('data.0.form.approval_at'),
                      'approval_reason' => $salesReturn->form->approval_reason,
                      'approval_status' => -1,
                      'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
                      'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
                      'request_cancellation_at' => $response->json('data.0.form.request_cancellation_at'),
                      'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
                      'cancellation_approval_at' => $response->json('data.0.form.cancellation_approval_at'),
                      'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
                      'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
                      'cancellation_status' => $salesReturn->form->cancellation_status,
                      'request_close_to' => $salesReturn->form->request_close_to,
                      'request_close_by' => $salesReturn->form->request_close_by,
                      'request_close_at' => $response->json('data.0.form.request_close_at'),
                      'request_close_reason' => $salesReturn->form->request_close_reason,
                      'close_approval_at' => $response->json('data.0.form.close_approval_at'),
                      'close_approval_by' => $salesReturn->form->close_approval_by,
                      'close_status' => $salesReturn->form->close_status,
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.0.form.id'),
            'number' => $response->json('data.0.form.number'),
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');

        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.0.form.number'),
            'table_id' => $response->json('data.0.id'),
            'table_type' => 'SalesReturn',
            'activity' => 'Rejected By Email'
        ], 'tenant');

        $subTotal = $response->json('data.0.amount') - $response->json('data.0.tax');
        $this->assertDatabaseMissing('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->arCoa->id,
            'credit' => $response->json('data.0.amount').'.000000000000000000000000000000'
        ], 'tenant');
        $this->assertDatabaseMissing('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->salesIncomeCoa->id,
            'debit' => $subTotal.'.000000000000000000000000000000'
        ], 'tenant');
        $this->assertDatabaseMissing('journals', [
            'form_id' => $response->json('data.0.form.id'),
            'chart_of_account_id' => $this->taxCoa->id,
            'debit' => $response->json('data.0.tax').'.000000000000000000000000000000'
        ], 'tenant');
    }

    /** @test */
    public function success_reject_delete_by_email_sales_return()
    {
        $this->delete_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $approver = $salesReturn->form->requestCancellationTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $salesReturn);

        $data = [
            'action' => 'reject',
            'approver_id' => $salesReturn->form->request_cancellation_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesReturn',
            'ids' => [
                ['id' => $salesReturn->id]
            ],
            'crud-type' => 'delete',
            'reason' => $this->faker->text(200)
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);
        $salesReturn = SalesReturn::where('id', $salesReturn->id)->first();
        $response->assertStatus(200)
        ->assertJson([
          'data' => [
                [
                    'id' => $salesReturn->id,
                    'sales_invoice_id' => $salesReturn->sales_invoice_id,
                    'warehouse_id' => $salesReturn->warehouse_id,
                    'customer_id' => $salesReturn->customer_id,
                    'customer_name' => $salesReturn->customer_name,
                    'customer_address' => $salesReturn->customer_address,
                    'customer_phone' => $salesReturn->customer_phone,
                    'tax' => $salesReturn->tax,
                    'amount' => $salesReturn->amount,
                    'form' => [
                    'id' => $salesReturn->form->id,
                    'date' => $response->json('data.0.form.date'),
                    'number' => $salesReturn->form->number,
                    'edited_number' => $salesReturn->form->edited_number, 
                    'edited_notes' => $salesReturn->form->edited_notes,
                    'notes' => $salesReturn->form->notes,
                    'created_by' => $salesReturn->form->created_by,
                    'updated_by' => $response->json('data.0.form.updated_by'),
                    'done' => $salesReturn->form->done,
                    'increment' => $salesReturn->form->increment,
                    'increment_group' => $salesReturn->form->increment_group,
                    'formable_id' => $salesReturn->form->formable_id,
                    'formable_type' => $salesReturn->form->formable_type,
                    'request_approval_at' => $response->json('data.0.form.request_approval_at'),
                    'request_approval_to' => $salesReturn->form->request_approval_to,
                    'approval_by' => $salesReturn->form->approval_by,
                    'approval_at' => $response->json('data.0.form.approval_at'),
                    'approval_reason' => $salesReturn->form->approval_reason,
                    'approval_status' => $salesReturn->form->approval_status,
                    'request_cancellation_to' => $salesReturn->form->request_cancellation_to,
                    'request_cancellation_by' => $salesReturn->form->request_cancellation_by,
                    'request_cancellation_at' => $response->json('data.0.form.request_cancellation_at'),
                    'request_cancellation_reason' => $salesReturn->form->request_cancellation_reason,
                    'cancellation_approval_at' => $response->json('data.0.form.cancellation_approval_at'),
                    'cancellation_approval_by' => $salesReturn->form->cancellation_approval_by,
                    'cancellation_approval_reason' => $salesReturn->form->cancellation_approval_reason,
                    'cancellation_status' => -1,
                    'request_close_to' => $salesReturn->form->request_close_to,
                    'request_close_by' => $salesReturn->form->request_close_by,
                    'request_close_at' => $response->json('data.0.form.request_close_at'),
                    'request_close_reason' => $salesReturn->form->request_close_reason,
                    'close_approval_at' => $response->json('data.0.form.close_approval_at'),
                    'close_approval_by' => $salesReturn->form->close_approval_by,
                    'close_status' => $salesReturn->form->close_status,
                ]            
            ]
          ]
        ]);
        $this->assertDatabaseHas('forms', [
            'number' => $salesReturn->form->number,
            'cancellation_status' => -1,
            'done' => 0
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $salesReturn->form->number,
            'table_id' => $salesReturn->id,
            'table_type' => 'SalesReturn',
            'activity' => 'Cancellation Rejected by Email'
        ], 'tenant');
    }
}
