<?php

namespace Tests\Feature\Master;

use App\Model\Master\PersonGroup;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonGroupValidationTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function a_person_group_code_and_name_should_be_unique()
    {
        $personGroup = factory(PersonGroup::class)->create();
        $data = [
            'code' => $personGroup->code,
            'name' => $personGroup->name,
        ];

        $response = $this->json('POST', 'api/v1/master/person-groups', $data, [$this->headers]);

        $response->assertJsonStructure([
            'error' => [
                'errors' => ['code', 'name']
            ]
        ]);

        $response->assertStatus(422);
    }
}
