<?php

namespace Tests\Feature\Http\Sales\SalesReturn;

use Tests\TestCase;

use App\Model\Form;
use App\Model\Sales\SalesReturn\SalesReturn;

class SalesReturnHistoryTest extends TestCase
{
    use SalesReturnSetup;

    public static $path = '/api/v1/sales/return';

    public function create_sales_return()
    {
        $this->setRole();

        $data = $this->getDummyData();

        $this->json('POST', self::$path, $data, $this->headers);
    }

    public function update_sales_return()
    {
        $this->create_sales_return();

        $salesReturn = SalesReturn::orderBy('id', 'asc')->first();
        
        $data = $this->getDummyData($salesReturn);
        $data = data_set($data, 'id', $salesReturn->id, false);

        $this->json('PATCH', self::$path . '/' . $salesReturn->id, $data, $this->headers);
    }

    /** @test */
    public function unauthorized_no_default_branch_read_histories()
    {
        $this->update_sales_return();

        $this->branchDefault->pivot->is_default = false;
        $this->branchDefault->pivot->save();

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

        $response->assertStatus(422)
        ->assertJson([
        'code' => 422,
        'message' => 'please set default branch to read this form'
        ]);
    }

    /** @test */
    public function unauthorized_create_sales_return()
    {
        $this->update_sales_return();

        $this->unsetUserRole();

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

        $response->assertStatus(500)
        ->assertJson([
            'code' => 0,
            'message' => 'There is no permission named `read sales return` for guard `api`.'
        ]);
    }

    /** @test */
    public function read_sales_return_histories()
    {
        $this->update_sales_return();

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
                'data' => [
                    [
                        'id',
                        'table_type',
                        'table_id',
                        'number',
                        'date',
                        'user_id',
                        'activity',
                        'formable_id',
                        'user' => [
                            'id',
                            'name',
                            'first_name',
                            'last_name',
                            'address',
                            'phone',
                            'email',
                            'branch_id',
                            'warehouse_id',
                            'full_name',
                        ],
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
        $this->create_sales_return();

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