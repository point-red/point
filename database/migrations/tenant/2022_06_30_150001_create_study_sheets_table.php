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
            // $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('user_id');
            $table->datetime('started_at');
            $table->datetime('ended_at');
            $table->string('photo_file_id')->nullable();
            $table->string('voice_note_file_id')->nullable();
            $table->string('video_file_id')->nullable();
            $table->foreignId('subject_id')->constrained('study_subjects');
            $table->string('institution')->nullable();
            $table->string('teacher')->nullable();
            $table->string('competency');
            $table->string('learning_goals');
            $table->string('activities')->nullable();
            $table->unsignedTinyInteger('grade')->nullable();
            $table->enum('behavior', ['A', 'B', 'C']);
            $table->string('remarks')->nullable();
            $table->boolean('is_draft');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
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
