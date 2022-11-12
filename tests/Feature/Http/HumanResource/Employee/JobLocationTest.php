<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use App\Model\HumanResource\Employee\EmployeeJobLocation;
use Tests\TestCase;

class JobLocationTest extends TestCase
{
    public static $path = '/api/v1/human-resource/employee/job-locations';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function getListEmployeeJobLocation()
    {
        $response = $this->json('GET', self::$path, [], $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function createEmployeeJobLocation()
    {
        $params = [
            'name' => $this->faker->text(10),
            'base_salary' => $this->faker->randomNumber(4),
            'multiplier_kpi' => $this->faker->randomNumber(4),
        ];

        $response = $this->json('POST', self::$path, $params, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('employee_job_locations', [
            'name' => $response->json('data.name'),
            'base_salary' => $response->json('data.base_salary'),
            'multiplier_kpi' => $response->json('data.multiplier_kpi'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function createEmployeeJobLocationInvalid()
    {
        $response = $this->json('POST', self::$path, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'base_salary' => ['The base salary field is required.'],
                    'multiplier_kpi' => ['The multiplier kpi field is required.'],
                ],
            ]);
    }

    /** @test */
    public function getEmployeeJobLocation()
    {
        $this->createEmployeeJobLocation();

        $model = EmployeeJobLocation::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function editEmployeeJobLocation()
    {
        $this->createEmployeeJobLocation();

        $model = EmployeeJobLocation::orderBy('id', 'asc')->first();

        $params = [
            'name' => $this->faker->text(15),
            'base_salary' => $this->faker->randomNumber(4),
            'multiplier_kpi' => $this->faker->randomNumber(4),
        ];

        $response = $this->json('PATCH', self::$path.'/'.$model->id, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('employee_job_locations', [
            'name' => $response->json('data.name'),
            'base_salary' => $response->json('data.base_salary'),
            'multiplier_kpi' => $response->json('data.multiplier_kpi'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function editEmployeeJobLocationInvalid()
    {
        $this->createEmployeeJobLocation();

        $model = EmployeeJobLocation::orderBy('id', 'asc')->first();

        $response = $this->json('PATCH', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'base_salary' => ['The base salary field is required.'],
                    'multiplier_kpi' => ['The multiplier kpi field is required.'],
                ],
            ]);
    }

    /** @test */
    public function deleteEmployeeJobLocation()
    {
        $this->createEmployeeJobLocation();

        $model = EmployeeJobLocation::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(204);
    }
}