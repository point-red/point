<?php

namespace Tests\Feature\Http\Master;

use App\Model\Master\FixedAsset;
use Tests\TestCase;

class FixedAssetTest extends TestCase
{
    static $path = '/api/v1/master/fixed-assets';

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function createData()
    {
        $data = [
            'code' => "".$this->faker->randomNumber(null, false),
            'name' => $this->faker->name,
            'depreciation_method' => FixedAsset::$DEPRECIATION_METHOD_STRAIGHT_LINE
        ];

        $response = $this->json('POST', FixedAssetTest::$path, $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('fixed_assets', $data, 'tenant');
    }

    /** @test */
    public function readSingleData()
    {
        $fixedAsset = factory(FixedAsset::class)->create();

        $response = $this->json('GET', FixedAssetTest::$path.'/'.$fixedAsset->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'code' => $fixedAsset->code,
                'name' => $fixedAsset->name,
                'depreciation_method' => $fixedAsset->depreciation_method,
            ],
        ]);
    }

    /** @test */
    public function readAllData()
    {
        $fixedAssets = factory(FixedAsset::class, 2)->create();

        $response = $this->json('GET', FixedAssetTest::$path, [], [$this->headers]);

        foreach ($fixedAssets as $fixedAsset) {
            $this->assertDatabaseHas('fixed_assets', [
                'code' => $fixedAsset->code,
                'name' => $fixedAsset->name,
                'depreciation_method' => $fixedAsset->depreciation_method,
            ], 'tenant');
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function updateData()
    {
        $fixedAsset = factory(FixedAsset::class)->create();

        $data = [
            'id' => $fixedAsset->id,
            'name' => $this->faker->name,
            'depreciation_method' => $fixedAsset->depreciation_method,
            'useful_life_year' => $this->faker->randomNumber(2),
            'salvage_value' => $this->faker->randomNumber()
        ];

        $response = $this->json('PUT', FixedAssetTest::$path.'/'.$fixedAsset->id, $data, [$this->headers]);

        $response->assertStatus(200);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('fixed_assets', $data, 'tenant');
    }

    /** @test */
    public function deleteData()
    {
        $fixedAsset = factory(FixedAsset::class)->create();

        $response = $this->json('DELETE', FixedAssetTest::$path.'/'.$fixedAsset->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('fixed_assets', [
            'code' => $fixedAsset->code,
            'name' => $fixedAsset->name,
        ], 'tenant');
    }
}
