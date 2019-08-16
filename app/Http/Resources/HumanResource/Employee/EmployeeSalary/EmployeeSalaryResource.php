<?php

namespace App\Http\Resources\HumanResource\Employee\EmployeeSalary;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HumanResource\Employee\EmployeeSalaryAssessment\EmployeeSalaryAssessmentResource;
use App\Http\Resources\HumanResource\Employee\EmployeeSalaryAchievement\EmployeeSalaryAchievementResource;

class EmployeeSalaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee' => new ApiResource($this->employee),
            'job_location' => $this->job_location,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'base_salary' => $this->base_salary,
            'multiplier_kpi' => $this->multiplier_kpi,
            'daily_transport_allowance' => $this->daily_transport_allowance,
            'functional_allowance' => $this->functional_allowance,
            'communication_allowance' => $this->communication_allowance,

            'active_days_in_month' => $this->active_days_in_month,

            'active_days_week1' => $this->active_days_week1,
            'active_days_week2' => $this->active_days_week2,
            'active_days_week3' => $this->active_days_week3,
            'active_days_week4' => $this->active_days_week4,
            'active_days_week5' => $this->active_days_week5,

            'receivable_cut_60_days_week1' => $this->receivable_cut_60_days_week1,
            'receivable_cut_60_days_week2' => $this->receivable_cut_60_days_week2,
            'receivable_cut_60_days_week3' => $this->receivable_cut_60_days_week3,
            'receivable_cut_60_days_week4' => $this->receivable_cut_60_days_week4,
            'receivable_cut_60_days_week5' => $this->receivable_cut_60_days_week5,

            'overdue_receivable_week1' => $this->overdue_receivable_week1,
            'overdue_receivable_week2' => $this->overdue_receivable_week2,
            'overdue_receivable_week3' => $this->overdue_receivable_week3,
            'overdue_receivable_week4' => $this->overdue_receivable_week4,
            'overdue_receivable_week5' => $this->overdue_receivable_week5,

            'payment_from_marketing_week1' => $this->payment_from_marketing_week1,
            'payment_from_marketing_week2' => $this->payment_from_marketing_week2,
            'payment_from_marketing_week3' => $this->payment_from_marketing_week3,
            'payment_from_marketing_week4' => $this->payment_from_marketing_week4,
            'payment_from_marketing_week5' => $this->payment_from_marketing_week5,

            'payment_from_sales_week1' => $this->payment_from_sales_week1,
            'payment_from_sales_week2' => $this->payment_from_sales_week2,
            'payment_from_sales_week3' => $this->payment_from_sales_week3,
            'payment_from_sales_week4' => $this->payment_from_sales_week4,
            'payment_from_sales_week5' => $this->payment_from_sales_week5,

            'payment_from_spg_week1' => $this->payment_from_spg_week1,
            'payment_from_spg_week2' => $this->payment_from_spg_week2,
            'payment_from_spg_week3' => $this->payment_from_spg_week3,
            'payment_from_spg_week4' => $this->payment_from_spg_week4,
            'payment_from_spg_week5' => $this->payment_from_spg_week5,

            'cash_payment_week1' => $this->cash_payment_week1,
            'cash_payment_week2' => $this->cash_payment_week2,
            'cash_payment_week3' => $this->cash_payment_week3,
            'cash_payment_week4' => $this->cash_payment_week4,
            'cash_payment_week5' => $this->cash_payment_week5,

            'weekly_sales_week1' => $this->weekly_sales_week1,
            'weekly_sales_week2' => $this->weekly_sales_week2,
            'weekly_sales_week3' => $this->weekly_sales_week3,
            'weekly_sales_week4' => $this->weekly_sales_week4,
            'weekly_sales_week5' => $this->weekly_sales_week5,

            'wa_daily_report_week1' => $this->wa_daily_report_week1,
            'wa_daily_report_week2' => $this->wa_daily_report_week2,
            'wa_daily_report_week3' => $this->wa_daily_report_week3,
            'wa_daily_report_week4' => $this->wa_daily_report_week4,
            'wa_daily_report_week5' => $this->wa_daily_report_week5,

            'maximum_salary_amount' => $this->maximum_salary_amount,

            'assessments' => EmployeeSalaryAssessmentResource::collection($this->assessments),
            'achievements' => EmployeeSalaryAchievementResource::collection($this->achievements),
        ];
    }
}
