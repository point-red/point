<?php

namespace Tests\Feature\Http\JobValue;

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\JobValue\JobValueScoreSetting;
use App\Model\HumanResource\Kpi\Kpi;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Tests for the COC / contract eligibility guard on submitting (save as
 * completed) a Job Value assessment.
 *
 * Business rules (per PMO):
 *   - COC value must be >= minimum_coc setting (default 17)
 *   - Latest contract must remain valid >= 6 months from the period start
 *   - Only enforced when status === 'completed'
 */
class JobValueEligibilityTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();

        $s = new JobValueScoreSetting();
        $s->minimum_kpi = 90;
        $s->minimum_coc = 17;
        $s->save();
    }

    private function makeEmployee(): Employee
    {
        return factory(Employee::class)->create();
    }

    private function makeContract(Employee $employee, string $end): void
    {
        $c = new EmployeeContract();
        $c->employee_id = $employee->id;
        $c->contract_begin = '2020-01-01';
        $c->contract_end = $end;
        $c->contract_due_date = $end;
        $c->save();
    }

    /** Create a COC (kpi + group + indicator) worth $score for the given year. */
    private function makeCoc(Employee $employee, string $date, float $score): void
    {
        $kpiId = DB::connection('tenant')->table('kpis')->insertGetId([
            'employee_id' => $employee->id,
            'scorer_id'   => $this->user->id,
            'name'        => 'coc',
            'date'        => $date,
            'status'      => 'COMPLETED',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        $groupId = DB::connection('tenant')->table('kpi_groups')->insertGetId([
            'kpi_id'     => $kpiId,
            'name'       => 'SOLUSI',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('kpi_indicators')->insert([
            'kpi_group_id'     => $groupId,
            'name'             => 'coc indicator',
            'weight'           => 100,
            'target'           => 25,
            'score'            => $score,
            'score_percentage' => 92,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    private function payload(Employee $employee, string $status): array
    {
        return [
            'employee_id'         => $employee->id,
            'period_from'         => '2026-01-01',
            'period_to'           => '2026-12-31',
            'status'              => $status,
            'request_approval_to' => $this->user->id,
            'total_value'         => 500,
            'total_score'         => 500,
            'scores'              => [],
        ];
    }

    /** @test */
    public function cannot_submit_completed_when_coc_below_minimum()
    {
        $employee = $this->makeEmployee();
        $this->makeContract($employee, '2030-01-01');   // long contract, fine
        $this->makeCoc($employee, '2026-06-01', 10);     // COC 10 < 17

        $response = $this->json('POST', '/api/v1/human-resource/job-value/assessment', $this->payload($employee, 'completed'), [$this->headers]);

        $response->assertStatus(422);
        $this->assertStringContainsString('COC', $response->json('message'));
    }

    /** @test */
    public function cannot_submit_completed_when_contract_shorter_than_six_months()
    {
        $employee = $this->makeEmployee();
        $this->makeContract($employee, '2026-03-01');    // ends < 6 months after 2026-01-01
        $this->makeCoc($employee, '2026-06-01', 20);     // COC ok

        $response = $this->json('POST', '/api/v1/human-resource/job-value/assessment', $this->payload($employee, 'completed'), [$this->headers]);

        $response->assertStatus(422);
        $this->assertStringContainsString('contract', $response->json('message'));
    }

    /** @test */
    public function draft_is_allowed_even_when_ineligible()
    {
        $employee = $this->makeEmployee();
        // no contract, no COC — would fail if enforced

        $response = $this->json('POST', '/api/v1/human-resource/job-value/assessment', $this->payload($employee, 'draft'), [$this->headers]);

        $response->assertStatus(201);
    }

    /** @test */
    public function can_submit_completed_when_eligible()
    {
        $employee = $this->makeEmployee();
        $this->makeContract($employee, '2030-01-01');
        $this->makeCoc($employee, '2026-06-01', 20);

        $response = $this->json('POST', '/api/v1/human-resource/job-value/assessment', $this->payload($employee, 'completed'), [$this->headers]);

        $response->assertStatus(201);
    }
}
