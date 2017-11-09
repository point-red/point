<?php

namespace Tests\Feature\Master;

use App\Model\Master\PersonCategory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonCategoryRESTTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->signIn();
    }
    
    /** @test */
    public function an_user_can_create_person_category()
    {
        $data = [
            'code' => 'code',
            'name' => 'name',
        ];

        $response = $this->json('POST', 'api/v1/master/person-categories', $data, [$this->headers]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('person_categories', $data);
    }
    
    /** @test */
    public function an_user_can_read_single_person_category()
    {
        $personCategory = factory(PersonCategory::class)->create();
        $response = $this->json('GET', 'api/v1/master/person-categories/'.$personCategory->id, [], [$this->headers]);

        $response->assertJson([
            "data" => [
                'code' => $personCategory->code,
                'name' => $personCategory->name,
            ],
        ]);
    }
    
    /** @test */
    public function an_user_can_read_all_person_category()
    {
        $personCategories = factory(PersonCategory::class, 2)->create();

        $response = $this->json('GET', 'api/v1/master/person-categories', [], [$this->headers]);

        foreach ($personCategories as $personCategory) {
            $this->assertDatabaseHas('person_categories', [
                'code' => $personCategory->code,
                'name' => $personCategory->name,
            ]);
        }

        $response->assertStatus(200);
    }
    
    /** @test */
    public function an_user_can_update_person_category()
    {
        $personCategory = factory(PersonCategory::class)->create();

        $data = [
            'id' => $personCategory->id,
            'code' => 'another code',
            'name' => 'another name',
        ];

        $response = $this->json('PUT', 'api/v1/master/person-categories/'.$personCategory->id, $data, [$this->headers]);

        $response->assertJson(['data' => $data]);

        $this->assertDatabaseHas('person_categories', $data);

        $response->assertStatus(200);
    }
    
    /** @test */
    public function an_user_can_delete_person_category()
    {
        $personCategory = factory(PersonCategory::class)->create();

        $response = $this->json('DELETE', 'api/v1/master/person-categories/'.$personCategory->id, [], [$this->headers]);

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'code' => $personCategory->code,
            'name' => $personCategory->name,
        ]);
    }
}
