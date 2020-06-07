<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayBookInstructionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_book_instruction_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instruction_id')->nullable();
            $table->longtext('number')->nullable();
            $table->longtext('name')->nullable();
            $table->boolean('status')->default(true);
            $table->longtext('steps')->nullable();
            $table->timestamps();

            $table->foreign('instruction_id')->references('id')->on('play_book_instructions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('play_book_instruction_histories');
    }
}
