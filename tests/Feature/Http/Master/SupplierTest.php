<?php

namespace Tests\Feature\Http\Master;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    
    public function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    public function test_import_supplier()
    {
        $file = new UploadedFile(
          base_path('tests/Import/import_master_supplier_test.xlsx'),
          'import.xlsx',
          'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
          null,
          true
        );

        $data1 = [
            'code' => 'SR001',
            'name' => 'Abi',
            'email'=> 'abi@gmail.com'
        ];
        $data2 = [
            'code' => 'SP006',
            'name' => 'Dika',
            'email'=> 'dika@gmail.com'
        ];

        $send = [
            'code' => 1,
            'name' => 2,
            'email' => 3,
            'address' => 4,
            'phone' => 5,
            'bank_branch' => 6,
            'bank_name' => 7,
            'bank_account_number' => 8,
            'bank_account_name' => 9,
            'start_row' => 2,
            'file' => $file,
        ];

        // API Request
        $response = $this->post('/api/v1/master/suppliers/import', $send, ['Content-Type:multipart/form-data']);
        // $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertDatabaseHas('suppliers', $data1, 'tenant');
        $this->assertDatabaseHas('suppliers', $data2, 'tenant');
    }

    public function test_export_supplier()
    {
        $response = $this->post('/api/v1/master/suppliers/export', [], ['Tenant:dev']);
        $response->assertStatus(200);
    }

}

