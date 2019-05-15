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
            $table->string('employee_code')->after('employee_group_id');
            $table->unsignedInteger('employee_status_id')->nullable()->index()->after('job_title');
            $table->unsignedInteger('employee_job_location_id')->nullable()->index()->after('employee_status_id');
            $table->double('daily_transport_allowance')->after('notes');
            $table->double('team_leader_allowance')->after('daily_transport_allowance');
            $table->double('communication_allowance')->after('team_leader_allowance');
        });

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
            $table->dropColumn('employee_code');
            $table->dropColumn('employee_status_id');
            $table->dropColumn('employee_job_location_id');
            $table->dropColumn('daily_transport_allowance');
            $table->dropColumn('team_leader_allowance');
            $table->dropColumn('communication_allowance');
        });
    }
}
