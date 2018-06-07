<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\Master\PersonGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonGroupTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_person_group()
    {
        $data = [
            'code' => 'code',
            'name' => 'name',
        ];

        $response = $this->json('POST', 'api/v1/master/person-groups', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('person_groups', $data);
    }

    /** @test */
    public function an_user_can_read_single_person_group()
    {
        $personGroup = factory(PersonGroup::class)->create();
        $response = $this->json('GET', 'api/v1/master/person-groups/'.$personGroup->id, [], [$this->headers]);

        $response->assertJson([
            'data' => [
                'code' => $personGroup->code,
                'name' => $personGroup->name,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_person_group()
    {
        $personGroups = factory(PersonGroup::class, 2)->create();

        $response = $this->json('GET', 'api/v1/master/person-groups', [], [$this->headers]);

        foreach ($personGroups as $personGroup) {
            $this->assertDatabaseHas('person_groups', [
                'code' => $personGroup->code,
                'name' => $personGroup->name,
            ]);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_person_group()
    {
        $personGroup = factory(PersonGroup::class)->create();

        $data = [
            'id' => $personGroup->id,
            'code' => 'another code',
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/master/person-groups/'.$personGroup->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('person_groups', $data);

        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_person_group()
    {
        $personGroup = factory(PersonGroup::class)->create();

        $response = $this->json('DELETE', 'api/v1/master/person-groups/'.$personGroup->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('person_groups', [
            'code' => $personGroup->code,
            'name' => $personGroup->name,
        ]);
    }
}
