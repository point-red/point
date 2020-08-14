<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayBookInstructionStepContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_book_instruction_step_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('step_id')->nullable();
            $table->unsignedBigInteger('glossary_id')->nullable();
            $table->longtext('content')->nullable();
            $table->timestamps();

            $table->foreign('step_id')->references('id')->on('play_book_instruction_steps')->onDelete('cascade');
            $table->foreign('glossary_id')->references('id')->on('play_book_glossaries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('play_book_instruction_step_contents');
    }
}
