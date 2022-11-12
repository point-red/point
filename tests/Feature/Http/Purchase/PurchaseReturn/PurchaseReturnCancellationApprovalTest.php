<?php

namespace Tests\Feature\Http\Purchase\PurchaseReturn;

use Tests\TestCase;

use App\Model\Purchase\PurchaseReturn\PurchaseReturn;

class PurchaseReturnCancellationApprovalTest extends TestCase
{
    use PurchaseReturnSetup;

    public static $path = '/api/v1/purchase/return';

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

        $purchasesReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('DELETE', self::$path . '/' . $purchasesReturn->id, $data, $this->headers);

        $response->assertStatus(204);
        $this->assertDatabaseHas('forms', [
            'number' => $purchasesReturn->form->number,
            'request_cancellation_reason' => $data['reason'],
            'cancellation_status' => 0,
        ], 'tenant');
    }

    /** @test */
    public function success_cancellation_approve_purchase_return()
    {
        $this->success_delete_purchase_return();

        $purchasesReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $purchasesReturn->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $purchasesReturn->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'PurchaseReturn',
            'activity' => 'Cancel Approved'
        ], 'tenant');
    }

    /** @test */
    public function error_already_approved_cancellation_approve_purchase_return()
    {
        $this->success_delete_purchase_return();

        $purchasesReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $purchasesReturn->form->cancellation_status = 1;
        $purchasesReturn->form->save();

        $response = $this->json('POST', self::$path . '/' . $purchasesReturn->id . '/cancellation-approve', [], $this->headers);
        $response->assertStatus(422)
          ->assertJson([
              "code" => 422,
              "message" => "Can't approve, form already cancelled!"
          ]);
    }

    /** @test */
    public function success_reject_cancellation_purchase_return()
    {
        $this->success_delete_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/cancellation-reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => -1,
            'done' => 0,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'PurchaseReturn',
            'activity' => 'Rejected'
        ], 'tenant');
    }

    /** @test */
    public function error_no_reason_reject_purchase_return()
    {
        $this->success_delete_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/cancellation-reject', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "The given data was invalid."
            ]);
    }

    /** @test */
    public function error_reason_more_than_255_character_reject_purchase_return()
    {
        $this->success_delete_purchase_return();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(300);

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/cancellation-reject', [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                "code" => 422,
                "message" => "Reason can\t more than 255 character!"
            ]);
    }

    /** @test */
    public function error_unauthorized_approve_cancellation_purchase_return()
    {
        $this->success_delete_purchase_return();

        $this->unsetUserRole();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve purchase return` for guard `api`."
            ]);
    }

    /** @test */
    public function error_unauthorized_reject_cancellation_purchase_return()
    {
        $this->success_delete_purchase_return();

        $this->unsetUserRole();

        $purchaseReturn = PurchaseReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $purchaseReturn->id . '/cancellation-reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve purchase return` for guard `api`."
            ]);
    }
}
