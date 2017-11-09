<?php

namespace Tests\Feature\Master;

use App\Model\Master\Person;
use App\Model\Master\PersonCategory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonRESTTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_person()
    {
        $personCategory = factory(PersonCategory::class)->create();

        $data = [
            'code' => 'code',
            'name' => 'name',
            'person_categories_id' => $personCategory->id,
        ];

        $response = $this->json('POST', 'api/v1/master/persons', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('persons', $data);
    }

    /** @test */
    public function an_user_can_read_single_person()
    {
        $person = factory(Person::class)->create();

        $response = $this->json('GET', 'api/v1/master/persons/'.$person->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'code' => $person->code,
                'name' => $person->name,
                'person_categories_id' => $person->person_categories_id,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_person()
    {
        $persons = factory(Person::class, 2)->create();

        $response = $this->json('GET', 'api/v1/master/persons', [], [$this->headers]);

        foreach ($persons as $person) {
            $this->assertDatabaseHas('persons', [
                'code' => $person->code,
                'name' => $person->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_person()
    {
        $person = factory(Person::class)->create();

        $data = [
            'id' => $person->id,
            'code' => 'another code',
            'name' => 'another name',
            'person_categories_id' => $person->person_categories_id,
        ];

        $response = $this->json('PUT', 'api/v1/master/persons/'.$person->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('persons', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_person()
    {
        $person = factory(Person::class)->create();

        $response = $this->json('DELETE', 'api/v1/master/persons/'.$person->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('persons', ['id' => $person->id]);
    }
}
