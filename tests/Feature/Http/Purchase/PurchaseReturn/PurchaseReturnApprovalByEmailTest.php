<?php

namespace Tests\Feature\Http\Purchase\PurchaseReturn;

use Tests\TestCase;

use App\Model\Purchase\PurchaseReturn\PurchaseReturn;
use App\Model\Token;
use App\User;

class PurchaseReturnApprovalByEmailTest extends TestCase
{
    use PurchaseReturnSetup;

    public static $path = '/api/v1/purchase/return';

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
    private function changeActingAs($tenantUser, $purchaseReturn)
    {
        $tenantUser->branches()->syncWithoutDetaching($purchaseReturn->form->branch_id);
        foreach ($tenantUser->branches as $branch) {
            $branch->pivot->is_default = true;
            $branch->pivot->save();
        }
        $tenantUser->warehouses()->syncWithoutDetaching($purchaseReturn->warehouse_id);
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
    public function success_create_purchase_return()
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
    public function success_delete_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path . '/' . $purchaseReturn->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $purchaseReturn->form->number,
            'request_cancellation_reason' => $data['reason'],
            'cancellation_status' => 0,
        ], 'tenant');
    }

    /** @test */
    public function success_approve_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/approve', [], $this->headers);
        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'PurchaseReturn',
            'activity' => 'Approved'
        ], 'tenant');
    }

    /** @test */
    public function success_approve_by_email_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $approver = $purchaseReturn->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $purchaseReturn);

        $data = [
            'action' => 'approve',
            'approver_id' => $purchaseReturn->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'PurchaseReturn',
            'ids' => [
                ['id' => $purchaseReturn->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $purchaseReturn->form->id,
            'number' => $purchaseReturn->form->number,
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $purchaseReturn->form->number,
            'table_id' => $purchaseReturn->id,
            'table_type' => 'PurchaseReturn',
            'activity' => 'Approved by Email'
        ], 'tenant');
        $this->assertDatabaseHas('inventories', [
          'form_id' => $purchaseReturn->form->id,
          'item_id' => $purchaseReturn->items()->first()->item_id,
          'quantity' => $purchaseReturn->items()->first()->quantity,
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_approve_by_email_purchase_return()
    {
        $this->success_create_purchase_return();

        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve purchase return` for guard `api`."
            ]);
    }    

    /** @test */
    public function success_approve_delete_by_email_purchase_return()
    {
        $this->success_delete_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $approver = $purchaseReturn->form->requestCancellationTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $purchaseReturn);

        $data = [
            'action' => 'approve',
            'approver_id' => $purchaseReturn->form->request_cancellation_to,
            'token' => $approverToken->token,
            'resource-type' => 'PurchaseReturn',
            'ids' => [
                ['id' => $purchaseReturn->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $purchaseReturn->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $purchaseReturn->form->number,
            'table_id' => $purchaseReturn->id,
            'table_type' => 'PurchaseReturn',
            'activity' => 'Cancellation Approved by Email'
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_reject_by_email_purchase_return()
    {
        $this->success_delete_purchase_return();

        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve purchase return` for guard `api`."
            ]);
    }

    /** @test */
    public function success_reject_by_email_purchase_return()
    {
        $this->success_create_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $approver = $purchaseReturn->form->requestApprovalTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $purchaseReturn);

        $data = [
            'action' => 'reject',
            'approver_id' => $purchaseReturn->form->request_approval_to,
            'token' => $approverToken->token,
            'resource-type' => 'PurchaseReturn',
            'ids' => [
                ['id' => $purchaseReturn->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $purchaseReturn->form->id,
            'number' => $purchaseReturn->form->number,
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $purchaseReturn->form->number,
            'table_id' => $purchaseReturn->id,
            'table_type' => 'PurchaseReturn',
            'activity' => 'Rejected by Email'
        ], 'tenant');
    }

    /** @test */
    public function success_reject_delete_by_email_purchase_return()
    {
        $this->success_delete_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $approver = $purchaseReturn->form->requestCancellationTo;
        $approverToken = $this->findOrCreateToken($approver);

        $this->changeActingAs($approver, $purchaseReturn);

        $data = [
            'action' => 'reject',
            'approver_id' => $purchaseReturn->form->request_cancellation_to,
            'token' => $approverToken->token,
            'resource-type' => 'PurchaseReturn',
            'ids' => [
                ['id' => $purchaseReturn->id]
            ],
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $purchaseReturn->form->number,
            'cancellation_status' => -1,
            'done' => 0
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $purchaseReturn->form->number,
            'table_id' => $purchaseReturn->id,
            'table_type' => 'PurchaseReturn',
            'activity' => 'Cancellation Rejected by Email'
        ], 'tenant');
    }
}
