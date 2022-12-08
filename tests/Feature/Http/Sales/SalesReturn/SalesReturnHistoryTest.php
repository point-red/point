<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Model\Form;
use App\Model\Sales\SalesReturn\SalesReturn;

class SalesReturnHistoryTest extends TestCase
{
    use SalesReturnSetup;

    public static $path = '/api/v1/sales/return';

    /** @test */
    public function success_create_sales_return()
    {
        $this->setRole();

        $data = $this->getDummyData();

        $response = $this->json('POST', self::$path, $data, $this->headers);

        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    "id" => $response->json('data.id'),
                    "form" => [
                        "id" => $response->json('data.form.id'),
                        "date" => $response->json('data.form.date'),
                        "number" => $response->json('data.form.number'),
                        "notes" => $response->json('data.form.notes'),
                    ]
                ]
            ]);
        $this->assertDatabaseHas('forms', [
            'id' => $response->json('data.form.id'),
            'number' => $response->json('data.form.number'),
            'approval_status' => 0,
            'done' => 0,
        ], 'tenant');
    }
    /** @test */
    public function success_update_sales_return()
    {
        $this->success_create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($salesReturn);
        $data = data_set($data, 'id', $salesReturn->id, false);

        $response = $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);

        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    "id" => $response->json('data.id'),
                    "form" => [
                        "id" => $response->json('data.form.id'),
                        "date" => $response->json('data.form.date'),
                        "number" => $response->json('data.form.number'),
                        "notes" => $response->json('data.form.notes'),
                    ]
                ]
            ]);
        $this->assertDatabaseHas('forms', [ 'edited_number' => $response->json('data.form.number') ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.form.number'),
            'table_id' => $response->json('data.id'),
            'table_type' => 'SalesReturn',
            'activity' => 'Update - 1'
        ], 'tenant');
    }
    /** @test */
    public function read_sales_return_histories()
    {
        $this->success_update_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $salesReturnUpdated = SalesReturn::orderBy('id', 'desc')->first();

        $data = [
            'sort_by' => '-user_activities.date',
            'includes' => 'user',
            'filter_like' => '{}',
            'or_filter_where_has_like[]' => '{"user":{}}',
            'limit' => 10,
            'page' => 1
        ];

        $response = $this->json('GET', self::$path . '/' . $salesReturnUpdated->id . '/histories', $data, $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "data" => [
                    [
                        "id",
                        "table_type",
                        "table_id",
                        "number",
                        "date",
                        "user_id",
                        "activity",
                        "formable_id",
                        "user",
                    ]
                ]
            ]);
        $this->assertGreaterThan(0, count($response->json('data')));
        $this->assertDatabaseHas('user_activities', [
            'number' => $salesReturn->form->edited_number,
            'table_id' => $salesReturn->id,
            'table_type' => $salesReturn::$morphName,
            'activity' => 'Created'
        ], 'tenant');
        $this->assertDatabaseHas('user_activities', [
            'number' => $salesReturnUpdated->form->number,
            'table_id' => $salesReturnUpdated->id,
            'table_type' => $salesReturnUpdated::$morphName,
            'activity' => 'Update - 1'
        ], 'tenant');
    }
    /** @test */
    public function success_create_sales_return_history()
    {
        $this->success_create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        $data = [
            "id" => $salesReturn->id,
            "activity" => "Printed"
        ];

        $response = $this->json('POST', self::$path . '/' . $salesReturn->id . '/histories', $data, $this->headers);

        $response->assertStatus(201)
            ->assertJson([
                "data" => [
                    "table_type" => 'SalesReturn',
                    "table_id" => $salesReturn->id,
                    "number" => $salesReturn->form->number,
                    "activity" => 'Printed',
                ]
            ]);
        
        $this->assertDatabaseHas('user_activities', [
            'number' => $response->json('data.number'),
            'table_id' => $response->json('data.table_id'),
            'table_type' => $response->json('data.table_type'),
            'activity' => $response->json('data.activity')
        ], 'tenant');
    }
}