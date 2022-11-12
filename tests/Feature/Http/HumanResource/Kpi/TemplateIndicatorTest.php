<?php

namespace Tests\Feature\Http\HumanResource\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use Tests\TestCase;

class TemplateIndicatorTest extends TestCase
{
    private $templateGroup;

    public static $path = '/api/v1/human-resource/kpi/template-indicators';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();

        $this->templateGroup = $this->createTemplateGroup();
    } 

    private function createTemplateGroup()
    {
        $template = factory(KpiTemplate::class)->create();

        $data = [
            [
                'kpi_template_id' => $template->id,
                'name' => $this->faker->text(10),
            ]
        ];

        KpiTemplateGroup::insert($data); 

        return KpiTemplateGroup::orderBy('id', 'asc')->first();
    }

    /** @test */
    public function getListTemplateIndicator()
    {
        $params = [
            'kpi_template_group_id' => $this->templateGroup->id,
        ];

        $response = $this->json('GET', self::$path, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function createTemplateIndicator()
    {
        $params = [
            'kpi_template_group_id' => $this->templateGroup->id,
            'name' => $this->faker->text(10),
            'weight' => $this->faker->randomNumber(4),
            'target' => $this->faker->randomNumber(4),
            'automated_indicator' => []
        ];

        $response = $this->json('POST', self::$path, $params, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_template_indicators', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function createTemplateIndicatorInvalid()
    {
        $response = $this->json('POST', self::$path, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'kpi_template_group_id' => ['The kpi template group id field is required.'],
                    'name' => ['The name field is required.'],
                ],
            ]);
    }

    /** @test */
    public function getTemplateIndicator()
    {
        $this->createTemplateIndicator();

        $model = KpiTemplateIndicator::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function editTemplateIndicator()
    {
        $this->createTemplateIndicator();

        $model = KpiTemplateIndicator::orderBy('id', 'asc')->first();

        $params = [
            'kpi_template_group_id' => $this->templateGroup->id,
            'name' => $this->faker->text(10),
            'weight' => $this->faker->randomNumber(4),
            'target' => $this->faker->randomNumber(4),
            'automated_indicator' => []
        ];

        $response = $this->json('PATCH', self::$path.'/'.$model->id, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('kpi_template_indicators', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function editTemplateIndicatorInvalid()
    {
        $this->createTemplateIndicator();

        $model = KpiTemplateIndicator::orderBy('id', 'asc')->first();

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
    public function deleteTemplateIndicator()
    {
        $this->createTemplateIndicator();

        $model = KpiTemplateIndicator::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }
}
