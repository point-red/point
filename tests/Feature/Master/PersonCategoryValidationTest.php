<?php

namespace Tests\Feature\Master;

use App\Model\Master\PersonCategory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonCategoryValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_person_category_code_and_name_should_be_unique()
    {
        $personCategory = factory(PersonCategory::class)->create();
        $data = [
            'code' => $personCategory->code,
            'name' => $personCategory->name,
        ];

        $response = $this->json('POST', 'api/v1/master/person-categories', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['code', 'name']
            ]
        ]);

        $response->assertStatus(422);
    }
}
