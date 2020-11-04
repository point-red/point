<?php

namespace App\Model\HumanResource\Employee;

use App\Model\TransactionModel;

class EmployeeSalary extends TransactionModel
{
    protected $connection = 'tenant';

    public static $alias = 'employee_salary';

    private $total_amount_receivable = 0;

    /**
     * Get the assessments for the salary.
     */
    public function assessments()
    {
        return $this->hasMany(EmployeeSalaryAssessment::class);
    }

    /**
     * Get the achievements for the salary.
     */
    public function achievements()
    {
        return $this->hasMany(EmployeeSalaryAchievement::class);
    }

    /**
     * Get the employee that owns the salary.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Returns the amount of weeks into the month a date is.
     * @param $date a YYYY-MM-DD formatted date
     * @param $rollover The day on which the week rolls over
     */
    public static function getWeekOfMonth($date, $rollover = 'monday')
    {
        $cut = substr($date, 0, 8);
        $daylen = 86400;

        $timestamp = strtotime($date);
        $first = strtotime($cut.'00');
        $elapsed = ($timestamp - $first) / $daylen;

        $weeks = 1;

        for ($i = 1; $i <= $elapsed; $i++) {
            $dayfind = $cut.(strlen($i) < 2 ? '0'.$i : $i);
            $daytimestamp = strtotime($dayfind);

            $day = strtolower(date('l', $daytimestamp));

            if ($day == strtolower($rollover)) {
                $weeks++;
            }
        }

        return $weeks;
    }

    /**
     * Get the salary additional data.
     */
    public function getAdditionalSalaryData($assessments, $achievements)
    {
        $score_percentages_assessments = [];

        $total_assessments = $total_achievements = [
            'weight' => 0,
            'week1' => 0,
            'week2' => 0,
            'week3' => 0,
            'week4' => 0,
            'week5' => 0,
        ];

        foreach ($assessments as $index => $indicator) {
            $score_percentages_assessments[$index] = [
                'week1' => 0,
                'week2' => 0,
                'week3' => 0,
                'week4' => 0,
                'week5' => 0,
            ];

            foreach ($indicator->targets as $target) {
                $week_of_month = $target['week_of_month'];

                foreach ($indicator->scores as $score) {
                    if ($week_of_month === $score['week_of_month']) {
                        $score_percentages_assessments[$index][$week_of_month] = $target['target'] > 0 ? $score['score'] / $target['target'] * 100 : 0;

                        if ($score_percentages_assessments[$index][$week_of_month] > 100 && stripos($indicator->name, 'pelunasan piutang') === false) {
                            $score_percentages_assessments[$index][$week_of_month] = 100;
                        }

                        $total_assessments[$week_of_month] += (float) $score_percentages_assessments[$index][$week_of_month] * $indicator->weight / 100;
                    }
                }
            }

            $total_assessments['weight'] += $indicator->weight;
        }

        foreach ($achievements as $achievement) {
            $total_achievements['week1'] += (float) $achievement->week1 * $achievement->weight / 100;
            $total_achievements['week2'] += (float) $achievement->week2 * $achievement->weight / 100;
            $total_achievements['week3'] += (float) $achievement->week3 * $achievement->weight / 100;
            $total_achievements['week4'] += (float) $achievement->week4 * $achievement->weight / 100;
            $total_achievements['week5'] += (float) $achievement->week5 * $achievement->weight / 100;
            $total_achievements['weight'] += $achievement->weight;
        }

        return [
            'score_percentages_assessments' => $score_percentages_assessments,
            'total_assessments' => $total_assessments,
            'total_achievements' => $total_achievements,
        ];
    }

    /**
     * Get the salary additional data.
     */
    public function getAdditionalSalaryDataShowBy($assessments, $achievements)
    {
        $score_percentages_assessments = [];

        $total_assessments = $total_achievements = [
            'weight' => 0,
            'week1' => 0,
            'week2' => 0,
            'week3' => 0,
            'week4' => 0,
            'week5' => 0,
        ];

        foreach ($assessments as $index => $indicator) {
            $score_percentages_assessments[$index] = [
                'week1' => 0,
                'week2' => 0,
                'week3' => 0,
                'week4' => 0,
                'week5' => 0,
            ];

            foreach ($indicator['targets'] as $target) {
                $week_of_month = $target['week_of_month'];

                foreach ($indicator['scores'] as $score) {
                    if ($week_of_month === $score['week_of_month']) {
                        $score_percentages_assessments[$index][$week_of_month] = $target['target'] > 0 ? $score['score'] / $target['target'] * 100 : 0;

                        if ($score_percentages_assessments[$index][$week_of_month] > 100 && stripos($indicator['name'], 'pelunasan piutang') === false) {
                            $score_percentages_assessments[$index][$week_of_month] = 100;
                        }

                        $total_assessments[$week_of_month] += (float) $score_percentages_assessments[$index][$week_of_month] * $indicator['weight'] / 100;
                    }
                }
            }

            $total_assessments['weight'] += $indicator['weight'];
        }

        foreach ($achievements as $achievement) {
            $total_achievements['week1'] += (float) $achievement['week1'] * $achievement['weight'] / 100;
            $total_achievements['week2'] += (float) $achievement['week2'] * $achievement['weight'] / 100;
            $total_achievements['week3'] += (float) $achievement['week3'] * $achievement['weight'] / 100;
            $total_achievements['week4'] += (float) $achievement['week4'] * $achievement['weight'] / 100;
            $total_achievements['week5'] += (float) $achievement['week5'] * $achievement['weight'] / 100;
            $total_achievements['weight'] += $achievement['weight'];
        }

        return [
            'score_percentages_assessments' => $score_percentages_assessments,
            'total_assessments' => $total_assessments,
            'total_achievements' => $total_achievements,
        ];
    }

    /**
     * Get the total salary amount received.
     */
    public function getCalculationSalaryData($additionalSalaryData)
    {
        $communication_allowance_week_1 = $this->communication_allowance;
        $communication_allowance_week_2 = 0;
        $communication_allowance_week_3 = 0;
        $communication_allowance_week_4 = 0;
        $communication_allowance_week_5 = 0;

        $functional_allowance_week_1 = $this->functional_allowance;
        $functional_allowance_week_2 = 0;
        $functional_allowance_week_3 = 0;
        $functional_allowance_week_4 = 0;
        $functional_allowance_week_5 = 0;

        $salary_final_score_week_1 = ((float) $additionalSalaryData['total_assessments']['week1'] + (float) $additionalSalaryData['total_achievements']['week1']) / 2;
        $salary_final_score_week_2 = ((float) $additionalSalaryData['total_assessments']['week2'] + (float) $additionalSalaryData['total_achievements']['week2']) / 2;
        $salary_final_score_week_3 = ((float) $additionalSalaryData['total_assessments']['week3'] + (float) $additionalSalaryData['total_achievements']['week3']) / 2;
        $salary_final_score_week_4 = ((float) $additionalSalaryData['total_assessments']['week4'] + (float) $additionalSalaryData['total_achievements']['week4']) / 2;
        $salary_final_score_week_5 = ((float) $additionalSalaryData['total_assessments']['week5'] + (float) $additionalSalaryData['total_achievements']['week5']) / 2;

        $baseSalaryPerWeek = $this->active_days_in_month > 0 ? $this->base_salary / $this->active_days_in_month : 0;
        $baseMultiplierKpiPerWeek = $this->active_days_in_month > 0 ? $this->multiplier_kpi / $this->active_days_in_month : 0;

        $percentageCallWeek1 = $additionalSalaryData['score_percentages_assessments'][0]['week1'] / 100;
        $percentageCallWeek2 = $additionalSalaryData['score_percentages_assessments'][0]['week2'] / 100;
        $percentageCallWeek3 = $additionalSalaryData['score_percentages_assessments'][0]['week3'] / 100;
        $percentageCallWeek4 = $additionalSalaryData['score_percentages_assessments'][0]['week4'] / 100;
        $percentageCallWeek5 = $additionalSalaryData['score_percentages_assessments'][0]['week5'] / 100;

        $active_days_percentage_week_1 = $percentageCallWeek1 * $this->active_days_week1;
        $active_days_percentage_week_2 = $percentageCallWeek2 * $this->active_days_week2;
        $active_days_percentage_week_3 = $percentageCallWeek3 * $this->active_days_week3;
        $active_days_percentage_week_4 = $percentageCallWeek4 * $this->active_days_week4;
        $active_days_percentage_week_5 = $percentageCallWeek5 * $this->active_days_week5;

        $base_salary_week_1 = $baseSalaryPerWeek * $active_days_percentage_week_1;
        $base_salary_week_2 = $baseSalaryPerWeek * $active_days_percentage_week_2;
        $base_salary_week_3 = $baseSalaryPerWeek * $active_days_percentage_week_3;
        $base_salary_week_4 = $baseSalaryPerWeek * $active_days_percentage_week_4;
        $base_salary_week_5 = $baseSalaryPerWeek * $active_days_percentage_week_5;

        $real_transport_allowance_week_1 = $this->daily_transport_allowance * $active_days_percentage_week_1;
        $real_transport_allowance_week_2 = $this->daily_transport_allowance * $active_days_percentage_week_2;
        $real_transport_allowance_week_3 = $this->daily_transport_allowance * $active_days_percentage_week_3;
        $real_transport_allowance_week_4 = $this->daily_transport_allowance * $active_days_percentage_week_4;
        $real_transport_allowance_week_5 = $this->daily_transport_allowance * $active_days_percentage_week_5;

        $real_transport_allowance_total = $real_transport_allowance_week_1 + $real_transport_allowance_week_2 + $real_transport_allowance_week_3 + $real_transport_allowance_week_4 + $real_transport_allowance_week_5;

        $multiplier_kpi_week_1 = $baseMultiplierKpiPerWeek * $active_days_percentage_week_1;
        $multiplier_kpi_week_2 = $baseMultiplierKpiPerWeek * $active_days_percentage_week_2;
        $multiplier_kpi_week_3 = $baseMultiplierKpiPerWeek * $active_days_percentage_week_3;
        $multiplier_kpi_week_4 = $baseMultiplierKpiPerWeek * $active_days_percentage_week_4;
        $multiplier_kpi_week_5 = $baseMultiplierKpiPerWeek * $active_days_percentage_week_5;

        $minimum_component_amount_week_1 = (float) $additionalSalaryData['total_assessments']['week1'] * $base_salary_week_1 / 100;
        $minimum_component_amount_week_2 = (float) $additionalSalaryData['total_assessments']['week2'] * $base_salary_week_2 / 100;
        $minimum_component_amount_week_3 = (float) $additionalSalaryData['total_assessments']['week3'] * $base_salary_week_3 / 100;
        $minimum_component_amount_week_4 = (float) $additionalSalaryData['total_assessments']['week4'] * $base_salary_week_4 / 100;
        $minimum_component_amount_week_5 = (float) $additionalSalaryData['total_assessments']['week5'] * $base_salary_week_5 / 100;

        $additional_component_point_week_1 = (float) $additionalSalaryData['total_achievements']['week1'] * $multiplier_kpi_week_1 / 100;
        $additional_component_point_week_2 = (float) $additionalSalaryData['total_achievements']['week2'] * $multiplier_kpi_week_2 / 100;
        $additional_component_point_week_3 = (float) $additionalSalaryData['total_achievements']['week3'] * $multiplier_kpi_week_3 / 100;
        $additional_component_point_week_4 = (float) $additionalSalaryData['total_achievements']['week4'] * $multiplier_kpi_week_4 / 100;
        $additional_component_point_week_5 = (float) $additionalSalaryData['total_achievements']['week5'] * $multiplier_kpi_week_5 / 100;

        $additional_component_amount_week_1 = $additional_component_point_week_1 * 1000;
        $additional_component_amount_week_2 = $additional_component_point_week_2 * 1000;
        $additional_component_amount_week_3 = $additional_component_point_week_3 * 1000;
        $additional_component_amount_week_4 = $additional_component_point_week_4 * 1000;
        $additional_component_amount_week_5 = $additional_component_point_week_5 * 1000;

        $total_component_amount_week_1 = $minimum_component_amount_week_1 + $additional_component_amount_week_1;
        $total_component_amount_week_2 = $minimum_component_amount_week_2 + $additional_component_amount_week_2;
        $total_component_amount_week_3 = $minimum_component_amount_week_3 + $additional_component_amount_week_3;
        $total_component_amount_week_4 = $minimum_component_amount_week_4 + $additional_component_amount_week_4;
        $total_component_amount_week_5 = $minimum_component_amount_week_5 + $additional_component_amount_week_5;

        $total_component_amount = $total_component_amount_week_1 + $total_component_amount_week_2 + $total_component_amount_week_3 + $total_component_amount_week_4 + $total_component_amount_week_5;

        $total_amount_week_1 = $total_component_amount_week_1 + $real_transport_allowance_week_1;
        $total_amount_week_2 = $total_component_amount_week_2 + $real_transport_allowance_week_2;
        $total_amount_week_3 = $total_component_amount_week_3 + $real_transport_allowance_week_3;
        $total_amount_week_4 = $total_component_amount_week_4 + $real_transport_allowance_week_4;
        $total_amount_week_5 = $total_component_amount_week_5 + $real_transport_allowance_week_5;

        $total_amount_received_week_1 = $total_amount_week_1;
        $total_amount_received_week_2 = $total_amount_week_2;
        $total_amount_received_week_3 = $total_amount_week_3;
        $total_amount_received_week_4 = $total_amount_week_4;
        $total_amount_received_week_5 = $total_amount_week_5;

        $total_amount_received = $total_amount_received_week_1 + $total_amount_received_week_2 + $total_amount_received_week_3 + $total_amount_received_week_4 + $total_amount_received_week_5 + $communication_allowance_week_1 + $communication_allowance_week_2 + $communication_allowance_week_3 + $communication_allowance_week_4 + $communication_allowance_week_5 + $functional_allowance_week_1 + $functional_allowance_week_2 + $functional_allowance_week_3 + $functional_allowance_week_4 + $functional_allowance_week_5;

        $receivable_week_1 = $this->payment_from_marketing_week1 + $this->payment_from_sales_week1 + $this->payment_from_spg_week1 + $this->cash_payment_week1;
        $receivable_week_2 = $this->payment_from_marketing_week2 + $this->payment_from_sales_week2 + $this->payment_from_spg_week2 + $this->cash_payment_week2;
        $receivable_week_3 = $this->payment_from_marketing_week3 + $this->payment_from_sales_week3 + $this->payment_from_spg_week3 + $this->cash_payment_week3;
        $receivable_week_4 = $this->payment_from_marketing_week4 + $this->payment_from_sales_week4 + $this->payment_from_spg_week4 + $this->cash_payment_week4;
        $receivable_week_5 = $this->payment_from_marketing_week5 + $this->payment_from_sales_week5 + $this->payment_from_spg_week5 + $this->cash_payment_week5;

        $company_profit_week_1 = 0.05 * $receivable_week_1;
        $company_profit_week_2 = 0.05 * $receivable_week_2;
        $company_profit_week_3 = 0.05 * $receivable_week_3;
        $company_profit_week_4 = 0.05 * $receivable_week_4;
        $company_profit_week_5 = 0.05 * $receivable_week_5;

        $settlement_difference_minus_amount_week_1 = $receivable_week_1 - $total_amount_received_week_1 - $communication_allowance_week_1 - $functional_allowance_week_1;
        $settlement_difference_minus_amount_week_2 = $receivable_week_2 - $total_amount_received_week_2 - $communication_allowance_week_2 - $functional_allowance_week_2;
        $settlement_difference_minus_amount_week_3 = $receivable_week_3 - $total_amount_received_week_3 - $communication_allowance_week_3 - $functional_allowance_week_3;
        $settlement_difference_minus_amount_week_4 = $receivable_week_4 - $total_amount_received_week_4 - $communication_allowance_week_4 - $functional_allowance_week_4;
        $settlement_difference_minus_amount_week_5 = $receivable_week_5 - $total_amount_received_week_5 - $communication_allowance_week_5 - $functional_allowance_week_5;

        $company_profit_difference_minus_amount_week_1 = $company_profit_week_1 - $total_amount_received_week_1 - $communication_allowance_week_1 - $functional_allowance_week_1;
        $company_profit_difference_minus_amount_week_2 = $company_profit_week_2 - $total_amount_received_week_2 - $communication_allowance_week_2 - $functional_allowance_week_2;
        $company_profit_difference_minus_amount_week_3 = $company_profit_week_3 - $total_amount_received_week_3 - $communication_allowance_week_3 - $functional_allowance_week_3;
        $company_profit_difference_minus_amount_week_4 = $company_profit_week_4 - $total_amount_received_week_4 - $communication_allowance_week_4 - $functional_allowance_week_4;
        $company_profit_difference_minus_amount_week_5 = $company_profit_week_5 - $total_amount_received_week_5 - $communication_allowance_week_5 - $functional_allowance_week_5;

        $day_average_divisor = 0;
        $total_minimum_component_score = 0;
        $total_additional_component_score = 0;
        $total_final_score = 0;
        $average_minimum_component_score = 0;
        $average_additional_component_score = 0;
        $average_final_score = 0;

        if ($this->active_days_week1 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week1'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week1'];
            $total_final_score += $salary_final_score_week_1;
        }
        if ($this->active_days_week2 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week2'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week2'];
            $total_final_score += $salary_final_score_week_2;
        }
        if ($this->active_days_week3 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week3'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week3'];
            $total_final_score += $salary_final_score_week_3;
        }
        if ($this->active_days_week4 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week4'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week4'];
            $total_final_score += $salary_final_score_week_4;
        }
        if ($this->active_days_week5 != 0) {
            $day_average_divisor++;
            $total_minimum_component_score += $additionalSalaryData['total_assessments']['week5'];
            $total_additional_component_score += $additionalSalaryData['total_achievements']['week5'];
            $total_final_score += $salary_final_score_week_5;
        }

        $average_minimum_component_score = $day_average_divisor != 0 ? $total_minimum_component_score / $day_average_divisor : 0;
        $average_additional_component_score = $day_average_divisor != 0 ? $total_additional_component_score / $day_average_divisor : 0;
        $average_final_score = $day_average_divisor != 0 ? $total_final_score / $day_average_divisor : 0;

        $total_payment = $receivable_week_1 + $receivable_week_2 + $receivable_week_3 + $receivable_week_4 + $receivable_week_5;

        $total_settlement_difference_minus_amount = $settlement_difference_minus_amount_week_1 + $settlement_difference_minus_amount_week_2 + $settlement_difference_minus_amount_week_3 + $settlement_difference_minus_amount_week_4 + $settlement_difference_minus_amount_week_5;

        $total_company_profit_difference_minus_amount = $company_profit_difference_minus_amount_week_1 + $company_profit_difference_minus_amount_week_2 + $company_profit_difference_minus_amount_week_3 + $company_profit_difference_minus_amount_week_4 + $company_profit_difference_minus_amount_week_5;

        $total_weekly_sales = $this->weekly_sales_week1 + $this->weekly_sales_week2 + $this->weekly_sales_week3 + $this->weekly_sales_week4 + $this->weekly_sales_week5;

        $total_amount_received_difference = $this->maximum_salary_amount - $total_amount_received;

        return [
            'base_salary_week_1' => $base_salary_week_1,
            'base_salary_week_2' => $base_salary_week_2,
            'base_salary_week_3' => $base_salary_week_3,
            'base_salary_week_4' => $base_salary_week_4,
            'base_salary_week_5' => $base_salary_week_5,
            'real_transport_allowance_week_1' => $real_transport_allowance_week_1,
            'real_transport_allowance_week_2' => $real_transport_allowance_week_2,
            'real_transport_allowance_week_3' => $real_transport_allowance_week_3,
            'real_transport_allowance_week_4' => $real_transport_allowance_week_4,
            'real_transport_allowance_week_5' => $real_transport_allowance_week_5,
            'real_transport_allowance_total' => $real_transport_allowance_total,
            'salary_final_score_week_1' => $salary_final_score_week_1,
            'salary_final_score_week_2' => $salary_final_score_week_2,
            'salary_final_score_week_3' => $salary_final_score_week_3,
            'salary_final_score_week_4' => $salary_final_score_week_4,
            'salary_final_score_week_5' => $salary_final_score_week_5,
            'minimum_component_amount_week_1' => $minimum_component_amount_week_1,
            'minimum_component_amount_week_2' => $minimum_component_amount_week_2,
            'minimum_component_amount_week_3' => $minimum_component_amount_week_3,
            'minimum_component_amount_week_4' => $minimum_component_amount_week_4,
            'minimum_component_amount_week_5' => $minimum_component_amount_week_5,
            'multiplier_kpi_week_1' => $multiplier_kpi_week_1,
            'multiplier_kpi_week_2' => $multiplier_kpi_week_2,
            'multiplier_kpi_week_3' => $multiplier_kpi_week_3,
            'multiplier_kpi_week_4' => $multiplier_kpi_week_4,
            'multiplier_kpi_week_5' => $multiplier_kpi_week_5,
            'additional_component_point_week_1' => $additional_component_point_week_1,
            'additional_component_point_week_2' => $additional_component_point_week_2,
            'additional_component_point_week_3' => $additional_component_point_week_3,
            'additional_component_point_week_4' => $additional_component_point_week_4,
            'additional_component_point_week_5' => $additional_component_point_week_5,
            'additional_component_amount_week_1' => $additional_component_amount_week_1,
            'additional_component_amount_week_2' => $additional_component_amount_week_2,
            'additional_component_amount_week_3' => $additional_component_amount_week_3,
            'additional_component_amount_week_4' => $additional_component_amount_week_4,
            'additional_component_amount_week_5' => $additional_component_amount_week_5,
            'total_component_amount_week_1' => $total_component_amount_week_1,
            'total_component_amount_week_2' => $total_component_amount_week_2,
            'total_component_amount_week_3' => $total_component_amount_week_3,
            'total_component_amount_week_4' => $total_component_amount_week_4,
            'total_component_amount_week_5' => $total_component_amount_week_5,
            'total_component_amount' => $total_component_amount,
            'total_amount_week_1' => $total_amount_week_1,
            'total_amount_week_2' => $total_amount_week_2,
            'total_amount_week_3' => $total_amount_week_3,
            'total_amount_week_4' => $total_amount_week_4,
            'total_amount_week_5' => $total_amount_week_5,
            'total_amount_received_week_1' => $total_amount_received_week_1,
            'total_amount_received_week_2' => $total_amount_received_week_2,
            'total_amount_received_week_3' => $total_amount_received_week_3,
            'total_amount_received_week_4' => $total_amount_received_week_4,
            'total_amount_received_week_5' => $total_amount_received_week_5,
            'total_amount_received' => $total_amount_received,
            'company_profit_week_1' => $company_profit_week_1,
            'company_profit_week_2' => $company_profit_week_2,
            'company_profit_week_3' => $company_profit_week_3,
            'company_profit_week_4' => $company_profit_week_4,
            'company_profit_week_5' => $company_profit_week_5,
            'settlement_difference_minus_amount_week_1' => $settlement_difference_minus_amount_week_1,
            'settlement_difference_minus_amount_week_2' => $settlement_difference_minus_amount_week_2,
            'settlement_difference_minus_amount_week_3' => $settlement_difference_minus_amount_week_3,
            'settlement_difference_minus_amount_week_4' => $settlement_difference_minus_amount_week_4,
            'settlement_difference_minus_amount_week_5' => $settlement_difference_minus_amount_week_5,
            'company_profit_difference_minus_amount_week_1' => $company_profit_difference_minus_amount_week_1,
            'company_profit_difference_minus_amount_week_2' => $company_profit_difference_minus_amount_week_2,
            'company_profit_difference_minus_amount_week_3' => $company_profit_difference_minus_amount_week_3,
            'company_profit_difference_minus_amount_week_4' => $company_profit_difference_minus_amount_week_4,
            'company_profit_difference_minus_amount_week_5' => $company_profit_difference_minus_amount_week_5,
            'average_minimum_component_score' => $average_minimum_component_score,
            'average_additional_component_score' => $average_additional_component_score,
            'average_final_score' => $average_final_score,
            'total_payment' => $total_payment,
            'total_settlement_difference_minus_amount' => $total_settlement_difference_minus_amount,
            'total_company_profit_difference_minus_amount' => $total_company_profit_difference_minus_amount,
            'total_weekly_sales' => $total_weekly_sales,
            'total_amount_received_difference' => $total_amount_received_difference,
        ];
    }
}
