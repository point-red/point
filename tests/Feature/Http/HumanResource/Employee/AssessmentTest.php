<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use Setup;

    private $employee;
    private $templateScore;
    private $templateIndicator;
    private $templateGroup;
    private $template;
    private $url;
 
    public function setUp(): void
    {
        ini_set('memory_limit', -1);

        parent::setUp();
        $this->signIn();

        $this->employee = $this->createEmployee();

        $this->url = $this->getUrl($this->employee->id);
    }

    private function getUrl($id)
    {
        return "/api/v1/human-resource/employee/employees/${id}/assessment";
    }

    /** @test */
    public function getListAssessment()
    {
        $params = [
            'type' => 'all',
        ];

        $response = $this->json('GET', $this->url, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    private function getParams()
    {
        $this->createTemplates();

        return [
            'date' => [
                'start' => $this->faker->dateTime('now')->format("Y-m-d"),
                'end' => $this->faker->dateTime('now')->format("Y-m-d"),
            ],
            'template' => [
                'name' => $this->template->name,
                'groups' => [
                    [
                        'name' => $this->templateGroup->name,
                        'indicators' => [
                            [
                                'name' => $this->templateIndicator->name,
                                'weight' => $this->faker->randomNumber(2),
                                'target' => $this->faker->randomNumber(2),
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
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
                'comment' => $this->faker->text(10),
            ],
        ];
    }

    private function createTemplates()
    {
        $this->templateScore = $this->createTemplateScore();
        $this->templateIndicator = KpiTemplateIndicator::orderBy('id', 'asc')->first();
        $this->templateGroup = KpiTemplateGroup::orderBy('id', 'asc')->first();
        $this->template = KpiTemplate::orderBy('id', 'asc')->first();
    }

    /** @test */
    public function createAssessment()
    {
        $params = $this->getParams();

        $response = $this->json('POST', $this->url, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('kpi_indicators', [
            'kpi_group_id' => $params['template']['groups'][0]['indicators'][0]['selected']['kpi_group_id'],
            'name' => $params['template']['groups'][0]['indicators'][0]['name'],
            'weight' => $params['template']['groups'][0]['indicators'][0]['weight'],
            'target' => $params['template']['groups'][0]['indicators'][0]['target'],
            'score' => $params['template']['groups'][0]['indicators'][0]['selected']['score'],
            'notes' => $params['template']['groups'][0]['indicators'][0]['selected']['notes'],
            'comment' => $params['template']['groups'][0]['indicators'][0]['selected']['comment'],
            'score_description' => $params['template']['groups'][0]['indicators'][0]['selected']['description'],
        ], 'tenant');
    }
}