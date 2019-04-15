<?php

namespace Tests\Feature\Inventory;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryTransferSendTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /**
     * @test
     */
    public function get_transfer_send_test()
    {
        $response = $this->json('GET', 'api/v1/inventory/transfer-send', [
            'ignore_empty' => true,
        ], [$this->headers]);

        $content = $response->getContent();
        $json = json_decode($content);
        log_object($json);

        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    /**
     * @test
     */
    public function create_transfer_send_fail_test()
    {

        $array_body = [
            'form' => [
                'date' => "2019-f-16 02:43:s",
                'warehouse_from' => "d",
                'warehouse_to' => "3152",
                'note' => "test",
            ],
            'items' => [
                0 => [
                    'item' => "asfa",
                    'name' => "Heber Conn",
                    'quantity' => "90",
                ],
                1 => [
                    'item' => "2004",
                    'name' => "Prof. Demarcus Terry V",
                    'quantity' => "80",
                ]
            ]
        ];
        $response = $this->json('POST', 'api/v1/inventory/transfer-send', $array_body, [$this->headers]);

        $content = $response->getContent();
        $json = json_decode($content);
        log_object($json);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 
                'errors' => ['form.date', 'form.warehouse_from', 'form.warehouse_to', 'items.0.item', 'items.1.item']
            ]);

    }

    /**
     * @test
     */
    public function create_transfer_send_test()
    {

        $array_body = [
            'form' => [
                'date' => "2019-04-16 02:43:00",
                'warehouse_from' => "1",
                'warehouse_to' => "2",
                'note' => "test",
            ],
            'items' => [
                0 => [
                    'item' => "2",
                    'name' => "Heber Conn",
                    'quantity' => "90",
                ],
                1 => [
                    'item' => "3",
                    'name' => "Prof. Demarcus Terry V",
                    'quantity' => "80",
                ]
            ]
        ];
        $response = $this->json('POST', 'api/v1/inventory/transfer-send', $array_body, [$this->headers]);

        $content = $response->getContent();
        $json = json_decode($content);
        log_object($json);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => [
                'form',
                'items',
            ]]);

        return ['id'=> $json->data->id, 'form_number'=> $json->data->form->number];
    }


    /**
     * @test
     * @depends create_transfer_send_test
     */
    public function get_transfer_send_detail_test(array $reference)
    {
        
        //test unknown id
        $response = $this->json('GET', 'api/v1/inventory/transfer-send/asasfas')
            ->assertStatus(404);

        $this->json('GET', 'api/v1/inventory/transfer-send/'.$reference['id'])
            ->assertStatus(200)
            ->assertJson(
                [
                'data' => [
                    'id' => $reference['id'],
                    'form' => [
                        'number' => $reference['form_number']
                    ],
                ],
                ]
            );

    }

}
