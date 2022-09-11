<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Model\Sales\SalesReturn\SalesReturn;

class SalesReturnCancellationApprovalTest extends TestCase
{
    use SalesReturnSetup;

    public static $path = '/api/v1/sales/return';

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
    public function unauthorized_cancellation_approve_sales_return()
    {
        $this->success_delete_sales_return();

        $this->unsetUserRole();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales return` for guard `api`."
            ]);
    }

    /** @test */
    public function invalid_state_cancellation_approve_sales_return()
    {
        $this->success_create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_cancellation_approve_sales_return()
    {
        $this->success_delete_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-approve', [], $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $salesReturn->form->number,
            'cancellation_status' => 1,
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesReturn',
            'activity' => 'Cancel Approved'
        ], 'tenant');
    }

    /** @test */
    public function unauthorized_cancellation_reject_sales_return()
    {
        $this->success_delete_sales_return();

        $this->unsetUserRole();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', [], $this->headers);

        $response->assertStatus(500)
            ->assertJson([
                "code" => 0,
                "message" => "There is no permission named `approve sales return` for guard `api`."
            ]);
    }

    /** @test */
    public function invalid_cancellation_reject_sales_return()
    {
        $this->success_delete_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', [], $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function invalid_state_cancellation_reject_sales_return()
    {
        $this->success_create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();

        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', $data, $this->headers);

        $response->assertStatus(422);
    }

    /** @test */
    public function success_reject_sales_return()
    {
        $this->success_delete_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $data['reason'] = $this->faker->text(200);

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/cancellation-reject', $data, $this->headers);

        $response->assertStatus(200);
        $this->assertDatabaseHas('forms', [
            'number' => $salesReturn->form->number,
            'cancellation_status' => -1,
            'done' => 0
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesReturn',
            'activity' => 'Cancel Rejected'
        ], 'tenant');
    }
}
