<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableJobValueAssessments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->table('job_value_assessments', function (Blueprint $table) {
            $table->unsignedInteger('prev_assessment_id')->index()->nullable();
            $table->decimal('score_diff', '65', 30)->default(0);
            $table->decimal('score_diff_pct', '65', 30)->default(0);
            $table->unsignedInteger('prev_area_value_id')->index()->nullable();
            $table->decimal('prev_area_value', '65', 30)->default(0);
            $table->unsignedInteger('area_value_id')->index()->nullable();
            $table->decimal('area_value', '65', 30)->default(0);
            $table->decimal('area_value_diff', '65', 30)->default(0);
            $table->decimal('area_value_pct', '65', 30)->default(0);
            $table->decimal('kpi_avg', '65', 30)->default(0);
            $table->decimal('current_point', '65', 30)->default(0);
            $table->decimal('next_year_point', '65', 30)->default(0);
            $table->decimal('basic_fee', '65', 30)->default(0);
            $table->decimal('prev_fee', '65', 30)->default(0);
            $table->decimal('fee_add_pct', '65', 30)->default(0);
            $table->decimal('additional_fee', '65', 30)->default(0);
            $table->decimal('net_fee', '65', 30)->default(0);
            $table->decimal('jv_up_high', '65', 30)->default(0);
            $table->decimal('jv_up_normal', '65', 30)->default(0);
            $table->decimal('jv_static_high', '65', 30)->default(0);
            $table->decimal('jv_static_normal', '65', 30)->default(0);

            $table->foreign('area_value_id')->references('id')->on('employee_area_values')->onDelete('restrict');
            $table->foreign('prev_area_value_id')->references('id')->on('employee_area_values')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->table('job_value_assessments', function (Blueprint $table) {
            $table->dropColumn('prev_assessment_id');
            $table->dropColumn('score_diff');
            $table->dropColumn('score_diff_pct');
            $table->dropColumn('prev_area_value_id');
            $table->dropColumn('prev_area_value');
            $table->dropColumn('area_value_id');
            $table->dropColumn('area_value');
            $table->dropColumn('area_value_diff');
            $table->dropColumn('area_value_pct');
            $table->dropColumn('kpi_avg');
            $table->dropColumn('current_point');
            $table->dropColumn('next_year_point');
            $table->dropColumn('basic_fee');
            $table->dropColumn('prev_fee');
            
            $table->dropColumn('fee_add_pct');
            $table->dropColumn('additional_fee');
            $table->dropColumn('net_fee');
            $table->dropColumn('jv_up_high');
            $table->dropColumn('jv_up_normal');
            $table->dropColumn('jv_static_high');
            $table->dropColumn('jv_static_normal');
        });
    }
}
