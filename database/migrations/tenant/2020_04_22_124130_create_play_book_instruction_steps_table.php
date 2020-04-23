<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayBookInstructionStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_book_instruction_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('instruction_id')->nullable();
            $table->string('name', 300)->nullable();
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('play_book_instruction_steps');
    }
}
