<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychotestKraepelinColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_kraepelin_columns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kraepelin_id')->index();
            $table->integer('current_first_number');
            $table->integer('current_second_number');
            $table->integer('correct')->default(0);
            $table->integer('count')->default(0);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            // References
            $table->foreign('kraepelin_id')
                ->references('id')
                ->on('psychotest_kraepelins')
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
        Schema::dropIfExists('psychotest_kraepelin_column');
    }
}
