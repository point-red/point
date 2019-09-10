<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEmployeeSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->double('weekly_sales_week1')->after('cash_payment_week5')->default(0);
            $table->double('weekly_sales_week2')->after('weekly_sales_week1')->default(0);
            $table->double('weekly_sales_week3')->after('weekly_sales_week2')->default(0);
            $table->double('weekly_sales_week4')->after('weekly_sales_week3')->default(0);
            $table->double('weekly_sales_week5')->after('weekly_sales_week4')->default(0);

            $table->double('wa_daily_report_week1')->after('weekly_sales_week5')->default(0);
            $table->double('wa_daily_report_week2')->after('wa_daily_report_week1')->default(0);
            $table->double('wa_daily_report_week3')->after('wa_daily_report_week2')->default(0);
            $table->double('wa_daily_report_week4')->after('wa_daily_report_week3')->default(0);
            $table->double('wa_daily_report_week5')->after('wa_daily_report_week4')->default(0);

            $table->double('maximum_salary_amount')->after('wa_daily_report_week5')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_salaries', function (Blueprint $table) {
            $table->dropColumn('weekly_sales_week1');
            $table->dropColumn('weekly_sales_week2');
            $table->dropColumn('weekly_sales_week3');
            $table->dropColumn('weekly_sales_week4');
            $table->dropColumn('weekly_sales_week5');

            $table->dropColumn('wa_daily_report_week1');
            $table->dropColumn('wa_daily_report_week2');
            $table->dropColumn('wa_daily_report_week3');
            $table->dropColumn('wa_daily_report_week4');
            $table->dropColumn('wa_daily_report_week5');

            $table->dropColumn('maximum_salary_amount');
        });
    }
}
