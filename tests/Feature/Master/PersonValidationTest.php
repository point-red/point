<?php

namespace Tests\Feature\Master;

use App\Model\Master\Person;
use App\Model\Master\PersonCategory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_person_name_with_person_categories_id_should_be_unique()
    {
        $personCategory = factory(PersonCategory::class)->create();
        $person = factory(Person::class)->create([
            'person_categories_id' => $personCategory->id
        ]);

        $response = $this->json('POST', 'api/v1/master/persons', [
            'code' => 'NEW CODE',
            'name' => $person->name,
            'person_categories_id' => $personCategory->id,
        ], [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['name']
            ]
        ]);

        $response->assertStatus(422);
    }
    
    /** @test */
    public function a_person_code_should_be_unique()
    {
        $personCategory = factory(PersonCategory::class)->create();
        $person = factory(Person::class)->create([
            'person_categories_id' => $personCategory->id
        ]);

        $response = $this->json('POST', 'api/v1/master/persons', [
            'code' => $person->code,
            'name' => 'NEW NAME',
            'person_categories_id' => $personCategory->id,
        ], [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['code']
            ]
        ]);

        $response->assertStatus(422);
    }
}
