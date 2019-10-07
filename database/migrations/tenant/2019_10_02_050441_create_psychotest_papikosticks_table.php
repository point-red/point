<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychotestPapikosticksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_papikosticks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id')->index();
            $table->timestamps();

            // Relationship
            $table->foreign('candidate_id')
                ->references('id')
                ->on('psychotest_candidates')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_papikosticks');
    }
}
