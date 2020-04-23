<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayBookInstructionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_book_instructions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procedure_id')->nullable();
            $table->string('number', 32)->nullable();
            $table->string('name', 300)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->foreign('procedure_id')->references('id')->on('play_book_procedures')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('play_book_instructions');
    }
}
