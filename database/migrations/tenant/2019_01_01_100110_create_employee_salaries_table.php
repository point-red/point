<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_id')->index();
            $table->string('job_location');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->double('base_salary');
            $table->double('multiplier_kpi');
            $table->double('daily_transport_allowance');
            $table->double('functional_allowance');
            $table->double('communication_allowance');

            $table->unsignedInteger('active_days_in_month')->index()->default(0);

            $table->unsignedInteger('active_days_week1')->index()->default(0);
            $table->unsignedInteger('active_days_week2')->index()->default(0);
            $table->unsignedInteger('active_days_week3')->index()->default(0);
            $table->unsignedInteger('active_days_week4')->index()->default(0);
            $table->unsignedInteger('active_days_week5')->index()->default(0);

            $table->double('receivable_cut_60_days_week1')->default(0);
            $table->double('receivable_cut_60_days_week2')->default(0);
            $table->double('receivable_cut_60_days_week3')->default(0);
            $table->double('receivable_cut_60_days_week4')->default(0);
            $table->double('receivable_cut_60_days_week5')->default(0);

            $table->double('overdue_receivable_week1')->default(0);
            $table->double('overdue_receivable_week2')->default(0);
            $table->double('overdue_receivable_week3')->default(0);
            $table->double('overdue_receivable_week4')->default(0);
            $table->double('overdue_receivable_week5')->default(0);

            $table->double('payment_from_marketing_week1')->default(0);
            $table->double('payment_from_marketing_week2')->default(0);
            $table->double('payment_from_marketing_week3')->default(0);
            $table->double('payment_from_marketing_week4')->default(0);
            $table->double('payment_from_marketing_week5')->default(0);

            $table->double('payment_from_sales_week1')->default(0);
            $table->double('payment_from_sales_week2')->default(0);
            $table->double('payment_from_sales_week3')->default(0);
            $table->double('payment_from_sales_week4')->default(0);
            $table->double('payment_from_sales_week5')->default(0);

            $table->double('payment_from_spg_week1')->default(0);
            $table->double('payment_from_spg_week2')->default(0);
            $table->double('payment_from_spg_week3')->default(0);
            $table->double('payment_from_spg_week4')->default(0);
            $table->double('payment_from_spg_week5')->default(0);

            $table->double('cash_payment_week1')->default(0);
            $table->double('cash_payment_week2')->default(0);
            $table->double('cash_payment_week3')->default(0);
            $table->double('cash_payment_week4')->default(0);
            $table->double('cash_payment_week5')->default(0);

            $table->double('weekly_sales_week1')->default(0);
            $table->double('weekly_sales_week2')->default(0);
            $table->double('weekly_sales_week3')->default(0);
            $table->double('weekly_sales_week4')->default(0);
            $table->double('weekly_sales_week5')->default(0);

            $table->double('wa_daily_report_week1')->default(0);
            $table->double('wa_daily_report_week2')->default(0);
            $table->double('wa_daily_report_week3')->default(0);
            $table->double('wa_daily_report_week4')->default(0);
            $table->double('wa_daily_report_week5')->default(0);

            $table->double('maximum_salary_amount')->default(0);

            $table->unsignedInteger('created_by')->index()->nullable();
            $table->unsignedInteger('updated_by')->index()->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_salaries');
    }
}
