<?php

namespace Tests\Feature\Http\Kpi;

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Kpi\Kpi;
use App\Model\HumanResource\Kpi\KpiGroup;
use App\Model\HumanResource\Kpi\KpiIndicator;
use Tests\TestCase;

class KpiAssessmentTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    public function create_assessment_test()
    {

        $employee = factory(Employee::class)->create();
        $employee_id = $employee->id;
        $indicator_name = 'test indicator';
        $data = [
            'template' => [
                'name' => 'test template',
                'groups' => [
                    [
                        'name' => 'test group',
                        'indicators' => [
                            [
                                'name' => $indicator_name,
                                'weight' => 5,
                                'target' => 5,
                                'selected' => [
                                    'id' => '1',
                                    'kpi_group_id' => '1',
                                    'score' => 5,
                                    'notes' => 'notes',
                                    'comment' => 'comment',
                                    'description' => 'sangat baik',
                                ],
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
            'date' => [
                'start' => date('Y-m-d'),
                'end' => date('Y-m-d')
            ],
        ];

        $response = $this->json('POST', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment', $data, [$this->headers]);
        $response->assertStatus(200);
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
        $employee = factory(Employee::class)->create();
        $employee_id = $employee->id;

        $response = $this->json('GET', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment', [], [$this->headers]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'meta',
            'data_set'
        ]);
    }

    /** @test */
    public function read_assessment_test()
    {
        $employee = factory(Employee::class)->create();
        $employee_id = $employee->id;


        $kpi = new Kpi();
        $kpi->employee_id = $employee_id;
        $kpi->date = date('Y-m-d');
        $kpi->name = 'test kpi';
        $kpi->scorer_id = $this->user->id;
        $kpi->status = 'DRAFT';
        $kpi->save();
        $kpi_id = $kpi->id;

        $response = $this->json('GET', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment/' . $kpi_id, [], [$this->headers]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    /** @test */
    public function get_by_periode_assessment_test()
    {
        $employee = factory(Employee::class)->create();
        $employee_id = $employee->id;

        $response = $this->json('GET', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment-by-periode/' . date("Y-m-d"), [], [$this->headers]);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    /** @test */
    public function show_by_assessment_test()
    {
        $employee = factory(Employee::class)->create();
        $employee_id = $employee->id;


        $kpi = new Kpi();
        $kpi->employee_id = $employee_id;
        $kpi->date = date('Y-m-d');
        $kpi->name = 'test kpi';
        $kpi->scorer_id = $this->user->id;
        $kpi->status = 'DRAFT';
        $kpi->save();
        $kpi_id = $kpi->id;

        $response = $this->json('GET', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment-by/' . $kpi_id, [], [$this->headers]);
        $response->assertStatus(200);
    }

    /** @test */
    public function update_assessment_test()
    {

        $employee = factory(Employee::class)->create();
        $employee_id = $employee->id;

        $kpi = new Kpi();
        $kpi->employee_id = $employee_id;
        $kpi->date = date('Y-m-d');
        $kpi->name = 'test kpi';
        $kpi->scorer_id = $this->user->id;
        $kpi->status = 'DRAFT';
        $kpi->save();
        $kpi_id = $kpi->id;

        $kpiGroup = new KpiGroup();
        $kpiGroup->kpi_id = $kpi_id;
        $kpiGroup->name = 'test groups';
        $kpiGroup->save();

        $kpiIndicator = new KpiIndicator();
        $kpiIndicator->kpi_group_id = $kpiGroup->id;
        $kpiIndicator->name = 'test indicator';
        $kpiIndicator->weight = 5;
        $kpiIndicator->target = 5;
        $kpiIndicator->score = 5;
        $kpiIndicator->score_percentage = $kpiIndicator->target > 0 ? $kpiIndicator->score / $kpiIndicator->target * $kpiIndicator->weight : 0;
        $kpiIndicator->score_description = 'sangat baik';
        $kpiIndicator->save();

        $data = [
            'template' => [
                'name' => 'test template',
                'groups' => [
                    [
                        'name' => 'test group',
                        'id' => $kpiGroup->id,
                        'indicators' => [
                            [
                                'id' => $kpiIndicator->id,
                                'name' => 'test indicator',
                                'weight' => 5,
                                'target' => 5,
                                'selected' => [
                                    'id' => '1',
                                    'kpi_group_id' => '1',
                                    'score' => 5,
                                    'notes' => 'notes',
                                    'comment' => 'comment',
                                    'description' => 'sangat baik',
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            'date' => date('Y-m-d')
        ];

        $response = $this->json('PUT', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment/' . $kpi_id, $data, [$this->headers]);
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
        $employee = factory(Employee::class)->create();
        $employee_id = $employee->id;


        $kpi = new Kpi();
        $kpi->employee_id = $employee_id;
        $kpi->date = date('Y-m-d');
        $kpi->name = 'test kpi';
        $kpi->scorer_id = $this->user->id;
        $kpi->status = 'DRAFT';
        $kpi->save();
        $kpi_id = $kpi->id;

        $response = $this->json('DELETE', '/api/v1/human-resource/employee/employees/' . $employee_id . '/assessment/' . $kpi_id, [], [$this->headers]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('kpis', [
            'employee_id' => $employee_id,
            'date' => $kpi->date,
            'name' => $kpi->name
        ], 'tenant');
    }
}
