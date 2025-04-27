<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableJobValueCriteriaAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->table('job_value_criterias', function (Blueprint $table) {
            $table->unsignedInteger('category_id')->after('id')->index()->nullable();

            $table->foreign('category_id')->references('id')->on('job_value_categories')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign(['category_id']);
        $table->dropColumn(['category_id']);
    }
}
