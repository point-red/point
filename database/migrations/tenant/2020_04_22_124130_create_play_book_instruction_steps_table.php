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
            $table->enum('approval_action', ['store', 'update', 'destroy'])->nullable();
            $table->unsignedInteger('approval_request_by')->nullable();
            $table->datetime('approval_request_at')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->unsignedInteger('approval_request_to')->nullable();
            $table->longtext('approval_note')->nullable();
            $table->unsignedBigInteger('instruction_step_pending_id')->nullable();
            $table->timestamps();

            $table->foreign('instruction_id')->references('id')->on('play_book_instructions')->onDelete('cascade');
            $table->foreign('instruction_step_pending_id')->references('id')->on('play_book_instruction_steps')->onDelete('cascade');
            $table->foreign('approval_request_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approval_request_to')->references('id')->on('users')->onDelete('cascade');
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
