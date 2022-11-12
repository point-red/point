<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use App\Model\HumanResource\Employee\EmployeeSalaryAdditionalComponent;
use Tests\TestCase;

class EmployeeSalaryAdditionalComponentTest extends TestCase
{
    public static $path = '/api/v1/human-resource/employee/additional-components';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function getListEmployeeSalaryAdditionalComponent()
    {
        $response = $this->json('GET', self::$path, [], $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function createEmployeeSalaryAdditionalComponent()
    {
        $params = [
            'name' => $this->faker->name,
            'weight' => $this->faker->randomNumber(3),
            'automated_code' => $this->faker->text(10),
            'automated_code_name' => $this->faker->text(10),
        ];

        $response = $this->json('POST', self::$path, $params, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('employee_salary_additional_components', [
            'name' => $response->json('data.name'),
            'weight' => $response->json('data.weight'),
            'automated_code' => $response->json('data.automated_code'),
            'automated_code_name' => $response->json('data.automated_code_name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function createEmployeeSalaryAdditionalComponentInvalid()
    {
        $response = $this->json('POST', self::$path, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'weight' => ['The weight field is required.'],
                    'automated_code' => ['The automated code field is required.'],
                    'automated_code_name' => ['The automated code name field is required.'],
                ],
            ]);
    }

    /** @test */
    public function getEmployeeSalaryAdditionalComponent()
    {
        $this->createEmployeeSalaryAdditionalComponent();

        $model = EmployeeSalaryAdditionalComponent::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function editEmployeeSalaryAdditionalComponent()
    {
        $this->createEmployeeSalaryAdditionalComponent();

        $model = EmployeeSalaryAdditionalComponent::orderBy('id', 'asc')->first();

        $params = [
            'name' => $this->faker->name,
            'weight' => $this->faker->randomNumber(3),
            'automated_code' => $this->faker->text(10),
            'automated_code_name' => $this->faker->text(10),
        ];

        $response = $this->json('PATCH', self::$path.'/'.$model->id, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('employee_salary_additional_components', [
            'name' => $response->json('data.name'),
            'weight' => $response->json('data.weight'),
            'automated_code' => $response->json('data.automated_code'),
            'automated_code_name' => $response->json('data.automated_code_name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function editEmployeeSalaryAdditionalComponentInvalid()
    {
        $this->createEmployeeSalaryAdditionalComponent();

        $model = EmployeeSalaryAdditionalComponent::orderBy('id', 'asc')->first();

        $response = $this->json('PATCH', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'weight' => ['The weight field is required.'],
                    'automated_code' => ['The automated code field is required.'],
                    'automated_code_name' => ['The automated code name field is required.'],
                ],
            ]);
    }

    /** @test */
    public function deleteEmployeeSalaryAdditionalComponent()
    {
        $this->createEmployeeSalaryAdditionalComponent();

        $model = EmployeeSalaryAdditionalComponent::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(204);
    }
}
