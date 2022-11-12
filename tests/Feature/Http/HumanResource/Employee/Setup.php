<?php

namespace Tests\Feature\Http\HumanResource\Employee;

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeGender;
use App\Model\HumanResource\Employee\EmployeeJobLocation;
use App\Model\HumanResource\Employee\EmployeeMaritalStatus;
use App\Model\HumanResource\Employee\EmployeeReligion;
use App\Model\HumanResource\Employee\EmployeeScorer;
use App\Model\HumanResource\Kpi\KpiTemplate;
use App\Model\HumanResource\Kpi\KpiTemplateGroup;
use App\Model\HumanResource\Kpi\KpiTemplateIndicator;
use App\Model\HumanResource\Kpi\KpiTemplateScore;

trait Setup
{
    public function createGender()
    {
        $data = [
            ['name' => 'Male'],
            ['name' => 'Female']
        ];

        EmployeeGender::insert($data);

        return EmployeeGender::orderBy('id', 'asc')->first();
    }

    public function createMaritalStatus()
    {
        $data = [
            ['name' => 'Single'],
            ['name' => 'Maried']
        ];

        EmployeeMaritalStatus::insert($data);

        return EmployeeMaritalStatus::orderBy('id', 'asc')->first();
    }

    public function createReligion()
    {
        $data = [
            ['name' => 'Christian'],
            ['name' => 'Catholic'],
            ['name' => 'Islam'],
            ['name' => 'Buddha'],
            ['name' => 'Hindu'],
        ];

        EmployeeReligion::insert($data);

        return EmployeeReligion::orderBy('id', 'asc')->first();
    }

    public function createJobLocation()
    {
        return factory(EmployeeJobLocation::class)->create();
    }

    public function createEmployee()
    {
        return factory(Employee::class)->create();
    }

    public function createTemplate()
    {
        return factory(KpiTemplate::class)->create();
    }

    public function createTemplateGroup()
    {
        $template = $this->createTemplate();

        $data = [
            'kpi_template_id' => $template->id,
            'name' => $this->faker->text(10),
        ];

        KpiTemplateGroup::insert([$data]); 
        return KpiTemplateGroup::orderBy('id', 'asc')->first();
    }

    private function createTemplateIndicator()
    {
        $group = $this->createTemplateGroup();

        $data = [
            'kpi_template_group_id' => $group->id,
            'name' => $this->faker->text(10),
            'weight' => $this->faker->randomNumber(4),
            'target' => $this->faker->randomNumber(4),
        ];

        KpiTemplateIndicator::insert([$data]); 
        return KpiTemplateIndicator::orderBy('id', 'asc')->first();
    }

    private function createTemplateScore()
    {
        $indicator = $this->createTemplateIndicator();

        $data = [
            'kpi_template_indicator_id' => $indicator->id,
            'description' => $this->faker->text(10),
            'score' => $this->faker->randomNumber(4)
        ];

        KpiTemplateScore::insert([$data]); 
        return KpiTemplateScore::orderBy('id', 'asc')->first();
    }
}