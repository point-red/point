<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEmpoloyeeJobLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_job_locations', function (Blueprint $table) {
            $table->unsignedDecimal('job_value', 65, 30)->nullable()->after('multiplier_kpi');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_job_locations', function (Blueprint $table) {
            //
        });
    }
}
