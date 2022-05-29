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
    }

    /** @test */
    public function success_delete_delivery_order()
    {
        $this->success_create_delivery_order();

        $deliveryOrder = DeliveryOrder::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path . '/' . $deliveryOrder->id, $data, $this->headers);

        $response->assertStatus(204);
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
            'ids' => $deliveryOrder->id,
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/approve', $data, $this->headers);
        $response->assertStatus(200);
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
    public function success_reject_delivery_order()
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
            'ids' => $deliveryOrder->id,
            'crud-type' => 'delete'
        ];

        $response = $this->json('POST', self::$path . '/reject', $data, $this->headers);

        $response->assertStatus(200);
    }
}
