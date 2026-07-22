<?php

namespace Tests\Feature\Http\JobValue;

use App\Model\HumanResource\Employee\Employee;
use App\Model\HumanResource\Employee\EmployeeAreaValue;
use App\Model\HumanResource\Employee\EmployeeContract;
use App\Model\HumanResource\Employee\EmployeeJobLocation;
use App\Model\HumanResource\JobValue\JobValueAssessment;
use App\Model\HumanResource\JobValue\JobValueScoreSetting;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Regression test for the "Employee JV Current Period" bug.
 *
 * Bug: JobValueAssessment::calculateFee() picked the previous assessment using
 * only (period_from < x AND approval_status = approved) WITHOUT filtering by
 * employee_id, so it could grab another employee's assessment as the
 * "current period" base — corrupting prev_assessment_id, prev_fee, basic_fee
 * and net_fee.
 *
 * Scenario replicated (the Helmi case):
 *   - Helmi  : approved JV, period 2026-01-01..2026-12-31, total_value 340.77
 *   - Other  : approved JV, period 2025-01-01..2025-12-31, total_value 390.54  (earlier period_from)
 *   - Helmi creates a NEW JV starting 2026-04-17
 *
 * Expected: the new assessment's prev_assessment must be HELMI's 340.77,
 * never the other employee's 390.54.
 */
class JobValueAssessmentFeeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /**
     * Build the dummy data shared by the tests below.
     *
     * @return array{helmi:Employee, other:Employee, location:EmployeeJobLocation}
     */
    private function seedDummyData(): array
    {
        // Required by calculateFee() (reads JobValueScoreSetting::first()).
        $setting = new JobValueScoreSetting();
        $setting->minimum_kpi = 90;
        $setting->minimum_coc = 17;
        $setting->save();

        $location = new EmployeeJobLocation();
        $location->name = 'Head Office';
        $location->base_salary = 5000000;
        $location->multiplier_kpi = 1;
        $location->save();

        // Area values (city minimum wage per year) so basic_fee can be computed.
        foreach ([2024, 2025, 2026, 2027] as $year) {
            $area = new EmployeeAreaValue();
            $area->job_location_id = $location->id;
            $area->year = $year;
            $area->value = 4300000 + (($year - 2024) * 400000); // 4.3M .. 5.5M
            $area->notes = 'umk ' . $year;
            $area->save();
        }

        $helmi = factory(Employee::class)->create(['name' => 'Helmi Ikhsan Fathkhurrahman']);
        $helmi->employee_job_location_id = $location->id;
        $helmi->save();

        $other = factory(Employee::class)->create(['name' => 'Employee Lain']);
        $other->employee_job_location_id = $location->id;
        $other->save();

        return ['helmi' => $helmi, 'other' => $other, 'location' => $location];
    }

    private function makeAssessment(Employee $employee, string $from, string $to, float $totalValue, string $approvalStatus): JobValueAssessment
    {
        $a = new JobValueAssessment();
        $a->employee_id = $employee->id;
        $a->period_from = $from;
        $a->period_to = $to;
        $a->total_value = $totalValue;
        $a->total_score = $totalValue;
        $a->status = 'completed';
        $a->approval_status = $approvalStatus;
        $a->save();

        return $a;
    }

    /** @test */
    public function prev_assessment_is_scoped_to_the_same_employee()
    {
        $data = $this->seedDummyData();

        // Another employee with an EARLIER period_from — this is the value that
        // leaked into "Employee JV Current Period" before the fix.
        $this->makeAssessment($data['other'], '2025-01-01', '2025-12-31', 390.54, 'approved');

        // Helmi's own valid prior JV.
        $helmiPrev = $this->makeAssessment($data['helmi'], '2026-01-01', '2026-12-31', 340.77, 'approved');

        // Helmi creates a NEW JV (the overlapping one from the bug report).
        $newAssessment = $this->makeAssessment($data['helmi'], '2026-04-17', '2027-04-17', 342.04, 'pending');

        JobValueAssessment::calculateFee($newAssessment);

        // Reload from DB with the relation to mirror what the API/UI reads.
        $result = JobValueAssessment::with('prevAssessment')->find($newAssessment->id);

        // The core assertion: previous assessment must be Helmi's own 340.77,
        // NOT the other employee's 390.54.
        $this->assertEquals($helmiPrev->id, $result->prev_assessment_id);
        $this->assertNotNull($result->prevAssessment, 'prevAssessment relation should resolve');
        $this->assertEquals(340.77, $result->prevAssessment->total_value);

        $this->assertDatabaseHas('job_value_assessments', [
            'id' => $newAssessment->id,
            'prev_assessment_id' => $helmiPrev->id,
        ], 'tenant');
    }

    /** @test */
    public function prev_assessment_is_null_when_employee_has_no_prior_approved_jv()
    {
        $data = $this->seedDummyData();

        // Only the OTHER employee has a prior approved JV.
        $this->makeAssessment($data['other'], '2025-01-01', '2025-12-31', 390.54, 'approved');

        $newAssessment = $this->makeAssessment($data['helmi'], '2026-04-17', '2027-04-17', 342.04, 'pending');

        $result = JobValueAssessment::calculateFee($newAssessment);

        $this->assertNull($result->prev_assessment_id);
        $this->assertEquals(0, $result->prev_fee);
        $this->assertEquals(0, $result->additional_fee);
    }

    /** @test */
    public function assessment_never_references_itself_as_previous()
    {
        $data = $this->seedDummyData();

        // A single approved JV for Helmi; recalculating it must not pick itself.
        $assessment = $this->makeAssessment($data['helmi'], '2026-01-01', '2026-12-31', 340.77, 'approved');

        $result = JobValueAssessment::calculateFee($assessment);

        $this->assertNotEquals($assessment->id, $result->prev_assessment_id);
        $this->assertNull($result->prev_assessment_id);
    }

    /** @test */
    public function prev_assessment_picks_latest_when_periods_are_equal()
    {
        $data = $this->seedDummyData();

        // Budi: period 1 (older) then period 2 & 3 sharing the same period_from.
        $this->makeAssessment($data['helmi'], '2025-01-01', '2025-12-31', 390.54, 'approved');
        $period2 = $this->makeAssessment($data['helmi'], '2026-07-01', '2026-12-31', 660.20, 'approved');

        // Period 3 reuses the same period_from as period 2 — the previous JV must
        // be period 2 (660.20), not the older period 1 (390.54).
        $period3 = $this->makeAssessment($data['helmi'], '2026-07-01', '2026-12-31', 700.00, 'pending');

        JobValueAssessment::calculateFee($period3);

        $result = JobValueAssessment::find($period3->id);
        $this->assertEquals($period2->id, $result->prev_assessment_id);
        $this->assertEquals(660.20, $result->prevAssessment->total_value);
    }

    /** Give an employee a valid contract + COC so a 'completed' submit passes eligibility. */
    private function makeEligible(Employee $employee, string $periodFrom): void
    {
        $c = new EmployeeContract();
        $c->employee_id = $employee->id;
        $c->contract_begin = '2020-01-01';
        $c->contract_end = '2030-01-01';
        $c->contract_due_date = '2030-01-01';
        $c->save();

        $year = substr($periodFrom, 0, 4);
        $kpiId = DB::connection('tenant')->table('kpis')->insertGetId([
            'employee_id' => $employee->id,
            'scorer_id'   => $this->user->id,
            'name'        => 'coc',
            'date'        => $year.'-06-01',
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
            'score'            => 20,
            'score_percentage' => 92,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /** @test */
    public function store_endpoint_sets_correct_prev_assessment()
    {
        $data = $this->seedDummyData();

        $this->makeAssessment($data['other'], '2025-01-01', '2025-12-31', 390.54, 'approved');
        $helmiPrev = $this->makeAssessment($data['helmi'], '2026-01-01', '2026-12-31', 340.77, 'approved');

        // Make Helmi eligible to submit (COC >= 17, contract >= 6 months).
        $this->makeEligible($data['helmi'], '2026-04-17');

        $payload = [
            'employee_id' => $data['helmi']->id,
            'period_from' => '2026-04-17',
            'period_to' => '2027-04-17',
            'status' => 'completed',
            'request_approval_to' => $this->user->id,
            'total_value' => 342.04,
            'total_score' => 342.04,
            'scores' => [],
        ];

        $response = $this->json('POST', '/api/v1/human-resource/job-value/assessment', $payload, [$this->headers]);
        $response->assertStatus(201);

        $created = JobValueAssessment::where('employee_id', $data['helmi']->id)
            ->where('period_from', '2026-04-17')
            ->first();

        $this->assertEquals($helmiPrev->id, $created->prev_assessment_id);
    }
}
