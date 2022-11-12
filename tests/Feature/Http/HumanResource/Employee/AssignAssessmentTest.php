<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use Tests\TestCase;

class AssignAssessmentTest extends TestCase
{
    use Setup;

    private $employee;
    private $template;
    private $url;
 
    public function setUp(): void
    {
        parent::setUp();
        $this->signIn();

        $this->employee = $this->createEmployee();
        $this->template = $this->createTemplate();

        $this->url = $this->getUrl($this->employee->id);
    }

    private function getUrl($id)
    {
        return "/api/v1/human-resource/employee/employees/${id}/assign-assessment";
    }

    /** @test */
    public function assignAssessementTest()
    {
        $params = [
            'kpi_template_id' => $this->template->id
        ];

        $response = $this->json('POST', $this->url, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }
}