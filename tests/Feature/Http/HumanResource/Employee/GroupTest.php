<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use App\Model\HumanResource\Employee\EmployeeGroup;
use Tests\TestCase;

class GroupTest extends TestCase
{
    public static $path = '/api/v1/human-resource/employee/groups';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function getListEmployeeGroup()
    {
        $response = $this->json('GET', self::$path, [], $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function createEmployeeGroup()
    {
        $params = ['name' => $this->faker->text(10)];

        $response = $this->json('POST', self::$path, $params, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('employee_groups', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function createEmployeeGroupInvalid()
    {
        $response = $this->json('POST', self::$path, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name field is required.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function getEmployeeGroup()
    {
        $this->createEmployeeGroup();

        $model = EmployeeGroup::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function editEmployeeGroup()
    {
        $this->createEmployeeGroup();

        $model = EmployeeGroup::orderBy('id', 'asc')->first();

        $params = ['name' => $this->faker->text(15)];

        $response = $this->json('PATCH', self::$path.'/'.$model->id, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('employee_groups', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function editEmployeeGroupInvalid()
    {
        $this->createEmployeeGroup();

        $model = EmployeeGroup::orderBy('id', 'asc')->first();

        $response = $this->json('PATCH', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name field is required.',
                    ],
                ],
            ]);
    }

    /** @test */
    public function deleteEmployeeGroup()
    {
        $this->createEmployeeGroup();

        $model = EmployeeGroup::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(204);
    }
}