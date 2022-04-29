<?php

namespace Tests\Feature\Http\Master;

use Tests\TestCase;

class CustomerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_customer_test()
    {
        $data = [
            'name' => $this->faker->name,
        ];

        // API Request
        $response = $this->json('POST', '/api/v1/master/customers', $data, [$this->headers]);

        // Check Status Response
        $response->assertStatus(201);

        // Check Database
        $this->assertDatabaseHas('customers', [
            'name' => $data['name'],
        ], 'tenant');
    }

    /** @test */
    public function user_can_download_customers_export_test()
    {
        $datetimenow = date("Y-m-d H:i:s");
        $data = array(
            'datetimenow' => $datetimenow,
            'group_id' => '1',
            'join' => 'address,phone,email'
        );
        $customer = new Customer();
        $customer->name = "Ramadhan";
        $customer->branch_id = $this->branch_id;
        $customer->save();

        $customerGroup = new CustomerGroup();
        $customerGroup->name = "Group1";
        $customerGroup->save();

        DB::connection('tenant')->table('customer_customer_group')->insert([
            'customer_id' => $customer->id,
            'customer_group_id' => $customerGroup->id,
            'created_at' => date("Y-m-d H:i:s")
        ]);

        $response = $this->json('POST', '/api/v1/master/customers/export', $data, $this->headers);
        $response->assertStatus(200);
        $nameUser = $this->user->name;
        $dateNow = date("Ymdhis", strtotime($datetimenow));
        $nameFile = $nameUser . '-' . $dateNow . '-customers.xlsx';
        $this->assertTrue($response->headers->get('Content-Type') == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertTrue($response->headers->get('Content-Disposition') == 'attachment; filename="' . $nameFile . '"');
    }
}
