<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\Master\Person;
use App\Model\Master\PersonCategory;
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
                'errors' => ['code', 'name'],
            ],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function a_person_category_has_many_person()
    {
        $numberOfPerson = 3;
        $personCategory = factory(PersonCategory::class)->create();

        factory(Person::class, $numberOfPerson)->create([
            'person_category_id' => $personCategory->id,
        ]);

        $personCategory = PersonCategory::withCount('persons')->find($personCategory->id);

        $this->assertTrue($numberOfPerson == $personCategory->persons_count);
    }
}
