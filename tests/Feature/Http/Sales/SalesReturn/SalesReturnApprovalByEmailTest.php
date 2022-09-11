<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Model\Sales\SalesReturn\SalesReturn;
use App\Model\Token;
use App\User;

class SalesReturnApprovalByEmailTest extends TestCase
{
    use SalesReturnSetup;

    public static $path = '/api/v1/sales/return';

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

    /** @test */
    public function success_create_sales_return()
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
    public function success_delete_sales_return()
    {
        $this->success_create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path . '/' . $salesReturn->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $salesReturn->form->number,
            'request_cancellation_reason' => $data['reason'],
            'cancellation_status' => 0,
        ], 'tenant');
    }

    /** @test */
    public function success_approve_sales_return()
    {
        $this->success_create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/approve', [], $this->headers);
        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesReturn',
            'activity' => 'Approved'
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_approve_by_email_sales_return()
    {
        $this->success_delete_sales_return();

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
        $this->success_create_sales_return();

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

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $salesReturn->form->id,
            'number' => $salesReturn->form->number,
            'approval_status' => 1
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $salesReturn->form->number,
            'table_id' => $salesReturn->id,
            'table_type' => 'SalesReturn',
            'activity' => 'Approved by Email'
        ], 'tenant');
        $this->assertDatabaseHas('inventories', [
          'form_id' => $salesReturn->form->id,
          'item_id' => $salesReturn->items()->first()->item_id,
          'quantity' => $salesReturn->items()->first()->quantity,
        ], 'tenant');
    }

    /** @test */
    public function success_approve_delete_by_email_sales_return()
    {
        $this->success_delete_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

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

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $salesReturn->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $salesReturn->form->number,
            'table_id' => $salesReturn->id,
            'table_type' => 'SalesReturn',
            'activity' => 'Cancellation Approved by Email'
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_reject_by_email_sales_return()
    {
        $this->success_delete_sales_return();

        $this->unsetUserRole();

        $response = $this->json('POST', self::$path . '/reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales return` for guard `api`."
            ]);
    }

    /** @test */
    public function success_reject_by_email_sales_return()
    {
        $this->success_create_sales_return();

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
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $salesReturn->form->id,
            'number' => $salesReturn->form->number,
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $salesReturn->form->number,
            'table_id' => $salesReturn->id,
            'table_type' => 'SalesReturn',
            'activity' => 'Rejected by Email'
        ], 'tenant');
    }

    /** @test */
    public function success_reject_delete_by_email_sales_return()
    {
        $this->success_delete_sales_return();

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
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
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
