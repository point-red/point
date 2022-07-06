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
            $table->datetime('started_at')->nullable();
            $table->datetime('ended_at')->nullable();
            $table->string('photo_file_id')->nullable();
            $table->string('audio_file_id')->nullable();
            $table->string('video_file_id')->nullable();
            $table->foreignId('subject_id')->nullable()->constrained('study_subjects');
            $table->string('institution')->nullable();
            $table->string('teacher')->nullable();
            $table->string('competency')->nullable();
            $table->string('learning_goals')->nullable();
            $table->string('activities')->nullable();
            $table->unsignedTinyInteger('grade')->nullable();
            $table->enum('behavior', ['A', 'B', 'C'])->nullable();
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
