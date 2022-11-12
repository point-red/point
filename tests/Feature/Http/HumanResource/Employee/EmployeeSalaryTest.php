<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use App\Model\HumanResource\Employee\Employee;
use Tests\TestCase;

class EmployeeSalaryTest extends TestCase
{
    private $employee;
    private $url;
 
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();

        $this->employee = factory(Employee::class)->create();

        $this->url = $this->getUrl($this->employee->id);
    }

    private function getUrl($id)
    {
        return "/api/v1/human-resource/employee/employees/${id}/salary";
    }

    /** @test */
    public function getListEmployeeSalary()
    {
        $params = [
            'type' => 'all'
        ];

        $response = $this->json('GET', $this->url, $params, $this->headers);

        $response->assertStatus(200);

        $this->assertArrayHasKey('data', $response->json());
    }
}