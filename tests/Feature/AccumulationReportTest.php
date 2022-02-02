<?php

namespace Tests\Feature;

use Tests\TestCase;

class AccumulationReportTest extends TestCase
{
    static $ENDPOINT = '/api/v1/plugin/pin-point/report/accumulation';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function get_interest_reasons()
    {
        $data = [
            'date' => date('Y-m-d'),
            'branchId'=>1,
            'filterId'=>1
        ];

        $response = $this->json('GET', self::$ENDPOINT, $data, [$this->headers]);

        $response->assertStatus(200);
    }

    /** @test */
    public function get_no_interest_reasons()
    {
        $data = [
            'date' => date('Y-m-d'),
            'branchId'=>1,
            'filterId'=>2
        ];

        $response = $this->json('GET', self::$ENDPOINT, $data, [$this->headers]);

        $response->assertStatus(200);
    }

     /** @test */
     public function get_similiar_product()
     {
         $data = [
             'date' => date('Y-m-d'),
             'branchId'=>1,
             'filterId'=>3
         ];
 
         $response = $this->json('GET', self::$ENDPOINT, $data, [$this->headers]);
 
         $response->assertStatus(200);
     }

     /** @test */
     public function get_repeat_order()
     {
         $data = [
             'date' => date('Y-m-d'),
             'branchId'=>1,
             'filterId'=>4
         ];
 
         $response = $this->json('GET', self::$ENDPOINT, $data, [$this->headers]);
 
         $response->assertStatus(200);
     }
}
