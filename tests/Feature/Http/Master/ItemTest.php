<?php

namespace Tests\Feature\Http\Master;
use Illuminate\Support\Facades\Artisan;

use Tests\TestCase;

class ItemTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('tenant:seed:dummy', ['db_name' => env('DB_TENANT_DATABASE')]);
        $this->signIn();
    }

    /** @test */
    public function import_item_test()
    {
        $chart_account = ['Persediaan Barang Jadi', 'Persediaan Dalam Perjalanan', 'Persediaan Dalam Bahan Mentah'];
        $units = [
            "Pcs" => 1,
            "Box" => 10,
            "Kardus" => 100,
        ];

        $payload = [];
        for($i=0;$i<3;$i++){
            $data = [
                'code' => $this->faker->randomNumber(null, false),
                'name' => $this->faker->name,
                'chart_of_account' => $chart_account[array_rand($chart_account)],
                'units' => [
                  [
                    'label' => array_rand($units),
                    'name' => array_rand($units),
                    'converter' => $units[array_rand($units)],
                    'default_purchase' => true,
                    'default_sales' => true
                  ],
                  [
                    'label' => array_rand($units),
                    'name' => array_rand($units),
                    'converter' => $units[array_rand($units)],
                    'default_purchase' => true,
                    'default_sales' => true
                  ]
                ],
                'require_expiry_date' => false,
                'require_production_number' => false,
                'group_name' => $this->faker->name
            ];
            array_push($payload, $data);
        }
        array_push($payload, []);

        $send['items'] = $payload;

        // API Request
        $response = $this->post('/api/v1/master/items/import', $send, [$this->headers]);
        // $response = $this->get('/');

        // Check Status Response
        $response->assertStatus(200);

    }
}
