<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychotestPositionCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_position_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_max')->default(15);
            $table->integer('category_min')->default(0);

            $table->unsignedInteger('position_id')->index();
            $table->unsignedInteger('category_id')->index();

            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            // Relationship
            $table->foreign('position_id')
                ->references('id')
                ->on('psychotest_candidate_positions')
                ->onDelete('cascade');
            $table->foreign('category_id')
                ->references('id')
                ->on('psychotest_papikostick_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_position_categories');
    }
}
