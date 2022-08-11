<?php

namespace Tests\Feature\Http\Sales\DeliveryOrder;

use Tests\TestCase;

use App\Model\Sales\DeliveryOrder\DeliveryOrder;
use App\Model\Token;
use App\User;

class DeliveryOrderApprovalByEmailTest extends TestCase
{
    use DeliveryOrderSetup;

    public static $path = '/api/v1/sales/delivery-orders';

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
    private function changeActingAs($tenantUser, $deliveryOrder)
    {
        $tenantUser->branches()->syncWithoutDetaching($deliveryOrder->form->branch_id);
        foreach ($tenantUser->branches as $branch) {
            $branch->pivot->is_default = true;
            $branch->pivot->save();
        }
        $tenantUser->warehouses()->syncWithoutDetaching($deliveryOrder->warehouse_id);
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

    /** @test */
    public function success_create_delivery_order()
    {
        $this->setRole();

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }

    /** @test */
    public function success_delete_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'request_cancellation_reason' => $data['reason'],
            'cancellation_status' => 0,
        ], 'tenant');
    }

    /** @test */
    public function success_approve_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/approve', [], $this->headers);
        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.sales_order.form.id'),
            'number' => $response->json('data.sales_order.form.number'),
            'done' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Approved'
        ], 'tenant');
    }

    /** @test */
    public function success_close_delivery_order()
    {
        $this->success_approve_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $deliveryOrder->id . '/close', $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'request_close_reason' => $data['reason'],
            'close_status' => 0,
            'done' => 0,
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_approve_by_email_delivery_order()
    {
        $this->success_delete_delivery_order();

        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
    }

    /** @test */
    public function success_approve_by_email_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $approver = $deliveryOrder->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $deliveryOrder);

        $data = [
            'action' => 'approve',
            'approver_id' => $deliveryOrder->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesDeliveryOrder',
            'ids' => [
                ['id' => $deliveryOrder->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $deliveryOrder->form->id,
            'number' => $deliveryOrder->form->number,
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'id' => $deliveryOrder->salesOrder->form->id,
            'number' => $deliveryOrder->salesOrder->form->number,
            'done' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryOrder->form->number,
            'table_id' => $deliveryOrder->id,
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Approved by Email'
        ], 'tenant');
    }

    /** @test */
    public function success_approve_delete_by_email_delivery_order()
    {
        $this->success_delete_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $approver = $deliveryOrder->form->requestCancellationTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $deliveryOrder);

        $data = [
            'action' => 'approve',
            'approver_id' => $deliveryOrder->form->request_cancellation_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesDeliveryOrder',
            'ids' => [
                ['id' => $deliveryOrder->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->salesOrder->form->number,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryOrder->form->number,
            'table_id' => $deliveryOrder->id,
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Cancellation Approved by Email'
        ], 'tenant');
    }
    
    /** @test */
    public function success_approve_close_by_email_delivery_order()
    {
        $this->success_close_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $approver = $deliveryOrder->form->requestCloseTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $deliveryOrder);

        $data = [
            'action' => 'approve',
            'approver_id' => $deliveryOrder->form->request_close_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesDeliveryOrder',
            'ids' => [
                ['id' => $deliveryOrder->id]
            ],
            'crud-type' => 'close'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'close_status' => 1,
            'done' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->salesOrder->form->number,
            'done' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryOrder->form->number,
            'table_id' => $deliveryOrder->id,
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Close Approved by Email'
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_reject_by_email_delivery_order()
    {
        $this->success_delete_delivery_order();

        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales delivery order` for guard `api`."
            ]);
    }
    /** @test */
    public function success_reject_by_email_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $approver = $deliveryOrder->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $deliveryOrder);

        $data = [
            'action' => 'reject',
            'approver_id' => $deliveryOrder->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesDeliveryOrder',
            'ids' => [
                ['id' => $deliveryOrder->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $deliveryOrder->form->id,
            'number' => $deliveryOrder->form->number,
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryOrder->form->number,
            'table_id' => $deliveryOrder->id,
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Rejected by Email'
        ], 'tenant');
    }
    /** @test */
    public function success_reject_delete_by_email_delivery_order()
    {
        $this->success_delete_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $approver = $deliveryOrder->form->requestCancellationTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $deliveryOrder);

        $data = [
            'action' => 'reject',
            'approver_id' => $deliveryOrder->form->request_cancellation_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesDeliveryOrder',
            'ids' => [
                ['id' => $deliveryOrder->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'cancellation_status' => -1,
            'done' => 0
        ], 'tenant');
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->salesOrder->form->number,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $deliveryOrder->form->number,
            'table_id' => $deliveryOrder->id,
            'table_type' => 'SalesDeliveryOrder',
            'activity' => 'Cancellation Rejected by Email'
        ], 'tenant');
    }
    /** @test */
    public function success_reject_close_by_email_delivery_order()
    {
        $this->success_close_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();

        $approver = $deliveryOrder->form->requestCloseTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $deliveryOrder);

        $data = [
            'action' => 'reject',
            'approver_id' => $deliveryOrder->form->request_close_to,
            'token' => $approverToken->token,
            'resource-type' => 'SalesDeliveryOrder',
            'ids' => [
                ['id' => $deliveryOrder->id]
            ],
            'crud-type' => 'close'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $deliveryOrder->form->number,
            'close_status' => -1
        ], 'tenant');
    }
}
