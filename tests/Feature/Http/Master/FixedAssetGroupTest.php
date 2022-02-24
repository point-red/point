<?php

namespace Tests\Feature\Http\Master;

use App\Model\Master\FixedAssetGroup;
use Tests\TestCase;

class FixedAssetGroupTest extends TestCase
{
    public static $path = '/api/v1/master/fixed-asset-groups/';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function createData()
    {
        $data = [
            'name' => $this->faker->name,
        ];

        $response = $this->json('POST', FixedAssetGroupTest::$path, $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('fixed_asset_groups', $data, 'tenant');
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function readSingleData()
    {
        $fixedAssetGroups = factory(FixedAssetGroup::class)->create();

        $response = $this->json('GET', FixedAssetGroupTest::$path.$fixedAssetGroups->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'name' => $fixedAssetGroups->name,
            ],
        ]);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function readAllData()
    {
        $fixedAssetGroupss = factory(FixedAssetGroup::class, 2)->create();

        $response = $this->json('GET', FixedAssetGroupTest::$path, [], [$this->headers]);

        foreach ($fixedAssetGroupss as $fixedAssetGroups) {
            $this->assertDatabaseHas('fixed_asset_groups', [
                'name' => $fixedAssetGroups->name,
            ], 'tenant');
        }

        $response->assertStatus(200);
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function updateData()
    {
        $fixedAssetGroups = factory(FixedAssetGroup::class)->create();

        $data = [
            'id' => $fixedAssetGroups->id,
            'name' => $this->faker->name,
        ];

        $response = $this->json('PUT', FixedAssetGroupTest::$path.$fixedAssetGroups->id, $data, [$this->headers]);

        $response->assertStatus(200);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('fixed_asset_groups', $data, 'tenant');
    }

    /**
     * @group ramadhani 
     * @test 
     */
    public function deleteData()
    {
        $fixedAssetGroups = factory(FixedAssetGroup::class)->create();

        $response = $this->json('DELETE', FixedAssetGroupTest::$path.$fixedAssetGroups->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('fixed_asset_groups', [
            'name' => $fixedAssetGroups->name,
        ], 'tenant');
    }
}
