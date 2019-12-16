<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychotestPapikostickResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_papikostick_results', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('total');
            $table->unsignedInteger('papikostick_id')->index();
            $table->unsignedInteger('category_id')->index()->nullable();
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            // Relationship
            $table->foreign('papikostick_id')
                ->references('id')
                ->on('psychotest_papikosticks')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('psychotest_papikostick_categories')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_papikostick_results');
    }
}
