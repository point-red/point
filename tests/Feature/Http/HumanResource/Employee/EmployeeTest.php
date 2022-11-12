<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeScorer;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use Setup;

    public static $path = '/api/v1/human-resource/employee/employees';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    private function parameters()
    {
        return [
            'name' => $this->faker->name,
            'company_emails' => [
                ['email' => $this->faker->email]
            ],
            'salary_histories' => [
                [
                    'salary' => $this->faker->randomNumber(9),
                    'date' => $this->faker->dateTime('now')->format("Y-m-d"),
                ]
            ],
            'social_media' => [
                [
                    'account' => $this->faker->text(10),
                    'type' => $this->faker->text(10),
                ]
            ],
            'contracts' => [
                [
                    'contract_begin' => $this->faker->dateTime('now')->format("Y-m-d"),
                    'contract_end' => $this->faker->dateTimeBetween('now', '+30 years')->format("Y-m-d"),
                    'notes' => $this->faker->text(50)
                ]
            ],
            'scorers' => [
                ['id' => $this->user->id]
            ]
        ];
    }

    /** @test */
    public function getListEmployee()
    {
        $params = [
            'additional' => 'groups',
            'scorer_id' => 1,
            'limit' => 10,
        ];

        $response = $this->json('GET', self::$path, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function createEmployee()
    {
        $params = $this->parameters();

        $response = $this->json('POST', self::$path, $params, $this->headers);
        
        $response->assertStatus(201);

        $this->assertDatabaseHas('employees', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function createEmployeeInvalid()
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
    public function getEmployee()
    {
        $this->createEmployee();

        $model = Employee::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function editEmployee()
    {
        $this->createEmployee();

        $model = Employee::orderBy('id', 'asc')->first();

        $params = $this->parameters();

        $response = $this->json('PATCH', self::$path.'/'.$model->id, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('employees', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function editEmployeeInvalid()
    {
        $this->createEmployee();

        $model = Employee::orderBy('id', 'asc')->first();

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
    public function deleteEmployee()
    {
        $this->createEmployee();

        $model = Employee::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function archiveEmployee()
    {
        $employee = factory(Employee::class)->create();

        $response = $this->json('PUT', self::$path.'/'.$employee->id.'/archive', [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function bulkArchiveEmployee()
    {
        $employees = factory(Employee::class, 3)->create();
        
        $params = [];
        foreach ($employees as $employee) {
            $params['employees'][] = ['id' => $employee->id];
        }

        $response = $this->json('PUT', self::$path.'/bulk-archive', $params, $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function activateEmployee()
    {
        $this->archiveEmployee();

        $employee = Employee::orderBy('id', 'asc')->first();

        $response = $this->json('PUT', self::$path.'/'.$employee->id.'/activate', [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function bulkactivateEmployee()
    {
        $this->bulkArchiveEmployee();

        $employees = Employee::all();
        
        $params = [];
        foreach ($employees as $employee) {
            $params['employees'][] = ['id' => $employee->id];
        }

        $response = $this->json('PUT', self::$path.'/bulk-activate', $params, $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function bulkDeleteEmployee()
    {
        $employees = factory(Employee::class, 3)->create();
        
        $params = [];
        foreach ($employees as $employee) {
            $params['employees'][] = ['id' => $employee->id];
        }

        $response = $this->json('PUT', self::$path.'/bulk-delete', $params, $this->headers);

        $response->assertStatus(204);
    }
}