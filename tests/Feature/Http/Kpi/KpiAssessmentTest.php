<?php

namespace Tests\Feature\Http\Kpi;

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Kpi\Kpi;
use Tests\TestCase;

class KpiAssessmentTest extends TestCase
{

    private $employee_id;

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_assessment_test()
    {
        $indicator_name = $this->faker('name');
        $data = [
            'template' => [
                'groups' => [
                    'name' => $this->faker('name'),
                    [
                        'indicators' => [
                            [
                                'id' => '1',
                                'kpi_group_id' => '1',
                                'name' => $indicator_name,
                                'weight' => 5,
                                'target' => 5,
                                'score' => 5,
                                'notes' => 'notes',
                                'comment' => 'comment',
                                'score_description' => 'sangat baik',
                                'scores' => [
                                    [
                                        "score" => 1,
                                        "description" => "sangat kurang"
                                    ],
                                    [
                                        "score" => 2,
                                        "description" => "kurang"
                                    ],
                                    [
                                        "score" => 3,
                                        "description" => "cukup"
                                    ],
                                    [
                                        "score" => 3,
                                        "description" => "baik"
                                    ],
                                    [
                                        "score" => 5,
                                        "description" => "sangat baik"
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            'date' => $this->faker->name,
        ];

        $employee = factory(Employee::class)->create();
        $this->employee_id = $employee->id;
        $response = $this->json('POST', '/api/v1/human-resource/employee/employees/' . $employee->id . '/assessment', $data, [$this->headers]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('kpi_indicators', [
            'kpi_group_id' => '1',
            'name' => $indicator_name,
            'weight' => 5,
            'target' => 5,
            'score' => 5,
            'notes' => 'notes',
            'comment' => 'comment',
            'score_description' => 'sangat baik',
        ], 'tenant');
    }

    /** @test */
    public function read_all_assessment_test()
    {
        $employee_id = $this->employee_id;

        $response = $this->json('GET', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment', [], [$this->headers]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'id',
                'name',
                'date',
                'employee',
                'weight',
                'target',
                'score',
                'score_percentage',
                'scorer',
                'groups',
            ]
        ]);
    }

    /** @test */
    public function read_assessment_test()
    {
        $employee_id = $this->employee_id;

        $response = $this->json('GET', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment/1', [], [$this->headers]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'date',
            'employee',
            'weight',
            'target',
            'score',
            'score_percentage',
            'scorer',
            'groups',
        ]);
    }

    /** @test */
    public function show_by_assessment_test()
    {
        $employee_id = $this->employee_id;

        $response = $this->json('GET', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment-by/1', [], [$this->headers]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            [
                'template',
                'date',
                'employee',
                'scorer',
                'group',
                'data',
                'total',
            ]
        ]);
    }

    /** @test */
    public function update_assessment_test()
    {
        $data = [
            'template' => [
                'groups' => [
                    'name' => $this->faker('name'),
                    [
                        'indicators' => [
                            [
                                'id' => '1',
                                'kpi_group_id' => '1',
                                'name' => $this->faker('name'),
                                'weight' => 5,
                                'target' => 5,
                                'score' => 5,
                                'notes' => 'notes',
                                'comment' => 'comment',
                                'score_description' => 'sangat baik',
                                'scores' => [
                                    [
                                        "score" => 1,
                                        "description" => "sangat kurang"
                                    ],
                                    [
                                        "score" => 2,
                                        "description" => "kurang"
                                    ],
                                    [
                                        "score" => 3,
                                        "description" => "cukup"
                                    ],
                                    [
                                        "score" => 3,
                                        "description" => "baik"
                                    ],
                                    [
                                        "score" => 5,
                                        "description" => "sangat baik"
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            'date' => $this->faker->name,
        ];
        $employee_id = $this->employee_id;
        $response = $this->json('PUT', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment', $data, [$this->headers]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'date',
            'employee',
            'weight',
            'target',
            'score',
            'score_percentage',
            'scorer',
            'groups',
        ]);
    }

    /** @test */
    public function delete_assessment_test()
    {
        $employee_id = $this->employee_id;
        $kpi = factory(Kpi::class)->create([
            'employee_id' => $employee_id,
            'date' => $this->faker('date'),
            'name' => $this->faker('name')
        ]);

        $response = $this->json('DELETE', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment/' . $kpi->id, [], [$this->headers]);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('kpis', [
            'employee_id' => $employee_id,
            'date' => $kpi->date,
            'name' => $kpi->name
        ], 'tenant');
    }
}
