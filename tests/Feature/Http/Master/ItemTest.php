<?php

namespace Tests\Feature\Http\Master;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\UploadedFile;

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
		$file = new UploadedFile(
			base_path('tests//import/import_master_item_test.xlsx'),
			'import.xlsx',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			null,
			true
		);

		$send = [
			'start_row' => 3,
			'code' => 3,
			'name' => 4,
			'chart_of_account' => 9,
			'units_measurement_1' => 5,
			'units_measurement_2' => 6,
			'units_converter_1' => 7,
			'units_converter_2' => 8,
			'require_expiry_date' => 11,
			'require_production_number' => 12,
			'group_name' => 10,
			'file' => $file
		];

		// API Request
		$response = $this->post('/api/v1/master/items/import', $send, ['Content-Type:multipart/form-data']);
		// $response = $this->get('/');

		// Check Status Response
		$response->assertStatus(200);
	}

	/** @test */
    public function export_items_test()
    {
        $response = $this->json('GET','/api/v1/master/items/export');
        
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'res' => true
            ]
        ]);
    }
}
