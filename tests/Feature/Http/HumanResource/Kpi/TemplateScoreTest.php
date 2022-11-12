<?php

namespace Tests\Feature\Http\HumanResource\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use App\Model\HumanResource\Kpi\KpiTemplateScore;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class TemplateScoreTest extends TestCase
{
    private $templateIndicator;

    public static $path = '/api/v1/human-resource/kpi/template-scores';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();

        $this->templateIndicator = $this->createTemplateIndicator();
        Log::info('check tempalet', [$this->templateIndicator]);
    } 

    private function createTemplateIndicator()
    {
        $template = factory(KpiTemplate::class)->create();

        $data = [
            'kpi_template_id' => $template->id,
            'name' => $this->faker->text(10),
        ];

        KpiTemplateGroup::insert([$data]); 
        $group = KpiTemplateGroup::orderBy('id', 'asc')->first();

        $data2 = [
            'kpi_template_group_id' => $group->id,
            'name' => $this->faker->text(10),
            'weight' => $this->faker->randomNumber(4),
            'target' => $this->faker->randomNumber(4),
        ];

        KpiTemplateIndicator::insert([$data2]); 
        return KpiTemplateIndicator::orderBy('id', 'asc')->first();
    }

    /** @test */
    public function getListTemplateScore()
    {
        $params = [
            'kpi_template_indicator_id' => $this->templateIndicator->id,
        ];

        $response = $this->json('GET', self::$path, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function createTemplateScore()
    {
        $params = [
            'kpi_template_indicator_id' => $this->templateIndicator->id,
            'description' => $this->faker->text(10),
            'score' => $this->faker->randomNumber(4)
        ];

        $response = $this->json('POST', self::$path, $params, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_template_scores', [
            'description' => $response->json('data.description'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function createTemplateScoreInvalid()
    {
        $response = $this->json('POST', self::$path, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'kpi_template_indicator_id' => ['The kpi template indicator id field is required.'],
                    'description' => ['The description field is required.'],
                ],
            ]);
    }

    /** @test */
    public function getTemplateScore()
    {
        $this->createTemplateScore();

        $model = KpiTemplateScore::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function editTemplateScore()
    {
        $this->createTemplateScore();

        $model = KpiTemplateScore::orderBy('id', 'asc')->first();

        $params = [
            'kpi_template_indicator_id' => $this->templateIndicator->id,
            'description' => $this->faker->text(10),
            'score' => $this->faker->randomNumber(4)
        ];

        $response = $this->json('PATCH', self::$path.'/'.$model->id, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('kpi_template_scores', [
            'description' => $response->json('data.description'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function editTemplateScoreInvalid()
    {
        $this->createTemplateScore();

        $model = KpiTemplateScore::orderBy('id', 'asc')->first();

        $response = $this->json('PATCH', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'kpi_template_indicator_id' => ['The kpi template indicator id field is required.'],
                    'description' => ['The description field is required.'],
                ],
            ]);
    }

    /** @test */
    public function deleteTemplateScore()
    {
        $this->createTemplateScore();

        $model = KpiTemplateScore::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }
}
