<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateKpiIndicatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_indicators', function (Blueprint $table) {
            $table->string('automated_id');
        });

        // Rearrange
        DB::statement('ALTER TABLE kpi_indicators MODIFY COLUMN automated_id TEXT AFTER score_description');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kpi_indicators', function (Blueprint $table) {
            $table->dropColumn('automated_id');
        });
    }
}
