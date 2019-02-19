<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Data related to job
            $table->string('employee_identity');
            $table->unsignedInteger('employee_status_id')->nullable()->index();
            $table->unsignedInteger('employee_job_location_id')->nullable()->index();
            $table->double('multiplier_kpi');
            $table->double('daily_transport_allowance');
            $table->double('tl_allowance');
            $table->double('communication_allowance');
        });

        // Rearrange
        DB::statement('ALTER TABLE employees MODIFY COLUMN employee_identity TEXT AFTER employee_group_id');
        DB::statement('ALTER TABLE employees MODIFY COLUMN employee_status_id INT UNSIGNED AFTER job_title');
        DB::statement('ALTER TABLE employees MODIFY COLUMN employee_job_location_id INT UNSIGNED AFTER employee_status_id');
        DB::statement('ALTER TABLE employees MODIFY COLUMN multiplier_kpi DOUBLE AFTER notes');
        DB::statement('ALTER TABLE employees MODIFY COLUMN daily_transport_allowance DOUBLE AFTER multiplier_kpi');
        DB::statement('ALTER TABLE employees MODIFY COLUMN tl_allowance DOUBLE AFTER daily_transport_allowance');
        DB::statement('ALTER TABLE employees MODIFY COLUMN communication_allowance DOUBLE AFTER tl_allowance');

        Schema::table('employees', function (Blueprint $table) {
            // Relationship
            $table->foreign('employee_status_id')->references('id')->on('employee_statuses')->onDelete('set null');
            $table->foreign('employee_job_location_id')->references('id')->on('employee_job_locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('employee_identity');
            $table->dropColumn('employee_status_id');
            $table->dropColumn('employee_job_location_id');
            $table->dropColumn('employee_base_salary_id');
            $table->dropColumn('multiplier_kpi');
            $table->dropColumn('daily_transport_allowance');
            $table->dropColumn('tl_allowance');
            $table->dropColumn('communication_allowance');
        });
    }
}
