<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychotestKraepelinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_kraepelins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('candidate_id')->index();
            $table->unsignedBigInteger('column_duration')->default(15000); // 15 seconds
            $table->integer('total_count')->default(0);
            $table->integer('total_correct')->default(0);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

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
        Schema::dropIfExists('psychotest_kraepelin');
    }
}
