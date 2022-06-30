<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudySheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_sheets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->datetime('date_start');
            $table->string('photo', 1000); // TODO adjust length according to actual needs
            $table->string('voice_note', 1000)->nullable(); // TODO adjust length according to actual needs
            $table->string('video', 1000)->nullable(); // TODO adjust length according to actual needs
            $table->foreignId('subject_id')->constrained('study_subjects');
            $table->string('institution')->nullable();
            $table->string('teacher')->nullable();
            $table->string('competency');
            $table->string('learning_goals');
            $table->string('activities')->nullable();
            $table->unsignedTinyInteger('grade')->nullable();
            $table->string('behavior');
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_sheets');
    }
}
