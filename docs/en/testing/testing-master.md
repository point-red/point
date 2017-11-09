# Testing Master

Each of master should have `Validation Test` and `REST Test` to make sure all code working as expected. 

## Validation Test

Validation Test is used to test database requirement like unique, required, min-max value, foreign-key (Relationship), etc.

Ex:

```php
/** @test */
public function a_person_code_should_be_unique()
{
    // relationship required
    $personCategory = factory(PersonCategory::class)->create();
    
    // create a fake person to database
    $person = factory(Person::class)->create([
        'person_category_id' => $personCategory->id,
    ]);
    
    // make an api request to create data
    // using code from another person from database
    // it should return error because code already taken
    $response = $this->json('POST', 'api/v1/master/persons', [
        'code' => $person->code,
        'name' => 'John Doe',
        'person_category_id' => $personCategory->id,
    ], [$this->headers]);

    // expect an error in column code
    $response->assertJsonStructure([
        'error' => [
            'errors' => ['code'],
        ],
    ]);

    // check if response status code is 422
    $response->assertStatus(422);
}
```

## REST Test

REST Test is used to make sure our CRUD is working as expected

Ex:

```php
<?php

namespace Tests\Feature\Master;

use Tests\TestCase;
use App\Model\Master\Person;
use App\Model\Master\PersonCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersonRESTTest extends TestCase
{
    // refresh database each test
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        
        // make sure user is authenticated
        $this->signIn();
    }

    /** @test */
    public function an_user_can_create_person()
    {
        // create a relationship data
        $personCategory = factory(PersonCategory::class)->create();

        // prepare our data
        $data = [
            'code' => 'CUSTOMER0001',
            'name' => 'John Doe',
            'person_category_id' => $personCategory->id,
        ];

        // sending data to create a new person
        $response = $this->json('POST', 'api/v1/master/persons', $data, [$this->headers]);

        // expect response status code 201 (Accepted)
        $response->assertStatus(201);

        // expect our request inserted into database
        $this->assertDatabaseHas('persons', $data);
    }

    /** @test */
    public function an_user_can_read_single_person()
    {
        // create a new person into database
        $person = factory(Person::class)->create();

        // send request to read person from server
        $response = $this->json('GET', 'api/v1/master/persons/'.$person->id, [], [$this->headers]);

        // expect our request return data from database
        $response->assertJson([
            'data' => [
                'code' => $person->code,
                'name' => $person->name,
                'person_category_id' => $person->person_category_id,
            ],
        ]);
    }

    /** @test */
    public function an_user_can_read_all_person()
    {
        // create a number of person into database
        $persons = factory(Person::class, 2)->create();

        // send request to read all person from server
        $response = $this->json('GET', 'api/v1/master/persons', [], [$this->headers]);

        // expect to see a number of person from our request
        foreach ($persons as $person) {
            $this->assertDatabaseHas('persons', [
                'code' => $person->code,
                'name' => $person->name,
            ]);
        }
        
        // expect response status is ok
        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_update_person()
    {
        // create a new person into database
        $person = factory(Person::class)->create();

        // prepare our data
        $data = [
            'id' => $person->id,
            'code' => 'CUSTOMER0002',
            'name' => 'John Moe',
            'person_category_id' => $person->person_category_id,
        ];

        // send request to update person from server
        $response = $this->json('PUT', 'api/v1/master/persons/'.$person->id, $data, [$this->headers]);

        // expect to return data
        $response->assertJson(['data' => $data]);

        // expect our new update request recorded in database
        $this->assertDatabaseHas('persons', $data);
        
        // expect response status ok
        $response->assertStatus(200);
    }

    /** @test */
    public function an_user_can_delete_person()
    {
        // create a new person to database
        $person = factory(Person::class)->create();

        // send request to delete data from server
        $response = $this->json('DELETE', 'api/v1/master/persons/'.$person->id, [], [$this->headers]);

        // expect status ok
        $response->assertStatus(204);

        // expect our data not exist in database
        $this->assertDatabaseMissing('persons', ['id' => $person->id]);
    }
}
```


