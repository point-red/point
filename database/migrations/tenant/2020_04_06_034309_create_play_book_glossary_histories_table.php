<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayBookGlossaryHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_book_glossary_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('glossary_id');
            $table->string('code', 32)->nullable();
            $table->string('name', 300)->nullable();
            $table->string('abbreviation', 300)->nullable();
            $table->longtext('note')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

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
        Schema::dropIfExists('play_book_glossary_histories');
    }
}
