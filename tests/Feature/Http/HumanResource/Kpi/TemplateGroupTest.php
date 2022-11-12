<?php

namespace Tests\Feature\Http\HumanResource\Kpi;

use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use Tests\TestCase;

class TemplateGroupTest extends TestCase
{
    private $template;

    public static $path = '/api/v1/human-resource/kpi/template-groups';
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();

        $this->template = factory(KpiTemplate::class)->create();
    } 

    /** @test */
    public function getListTemplateGroup()
    {
        $params = [
            'kpi_template_id' => $this->template->id,
        ];

        $response = $this->json('GET', self::$path, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }

    /** @test */
    public function createTemplateGroup()
    {
        $params = [
            'kpi_template_id' => $this->template->id,
            'name' => $this->faker->text(10),
        ];

        $response = $this->json('POST', self::$path, $params, $this->headers);

        $response->assertStatus(201);

        $this->assertDatabaseHas('kpi_template_groups', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function createTemplateGroupInvalid()
    {
        $response = $this->json('POST', self::$path, [], $this->headers);

        $response->assertStatus(422)
            ->assertJson([
                'code' => 422,
                'message' => 'The given data was invalid.',
                'errors' => [
                    'kpi_template_id' => ['The kpi template id field is required.'],
                    'name' => ['The name field is required.'],
                ],
            ]);
    }

    /** @test */
    public function getTemplateGroup()
    {
        $this->createTemplateGroup();

        $model = KpiTemplateGroup::orderBy('id', 'asc')->first();

        $response = $this->json('GET', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }

    /** @test */
    public function editTemplateGroup()
    {
        $this->createTemplateGroup();

        $model = KpiTemplateGroup::orderBy('id', 'asc')->first();

        $params = [
            'kpi_template_id' => $this->template->id,
            'name' => $this->faker->text(10),
        ];

        $response = $this->json('PATCH', self::$path.'/'.$model->id, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertDatabaseHas('kpi_template_groups', [
            'name' => $response->json('data.name'),
            'created_by' => $this->user->id
        ], 'tenant');
    }

    /** @test */
    public function editTemplateGroupInvalid()
    {
        $this->createTemplateGroup();

        $model = KpiTemplateGroup::orderBy('id', 'asc')->first();

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
    public function deleteTemplateGroup()
    {
        $this->createTemplateGroup();

        $model = KpiTemplateGroup::orderBy('id', 'asc')->first();

        $response = $this->json('DELETE', self::$path.'/'.$model->id, [], $this->headers);

        $response->assertStatus(200);
    }
}
