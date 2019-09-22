<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychologyKraeplinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychology_kraeplins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id')->index();
            $table->unsignedBigInteger('column_duration');
            $table->integer('total_count');
            $table->integer('total_correct');
            $table->timestamps();

            // Relationship
            $table->foreign('candidate_id')
                ->references('id')
                ->on('psychology_candidates')
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
        Schema::dropIfExists('psychology_kraeplin');
    }
}
