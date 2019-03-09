<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateKpiTemplateIndicatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpi_template_indicators', function (Blueprint $table) {
            $table->string('automated_id');
        });

        // Rearrange
        DB::statement('ALTER TABLE kpi_template_indicators MODIFY COLUMN automated_id TEXT AFTER target');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kpi_template_indicators', function (Blueprint $table) {
            $table->dropColumn('automated_id');
        });
    }
}
