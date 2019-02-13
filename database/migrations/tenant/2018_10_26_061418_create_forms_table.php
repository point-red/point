<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('date');
            $table->string('number')->nullable()->unique();
            $table->string('edited_number')->nullable();
            $table->text('edited_notes')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->boolean('done')->default(false);

            $table->unsignedInteger('formable_id');
            $table->string('formable_type');

            // Status approval
            // null = pending, true = approved, false = rejected
            $table->boolean('approved')->nullable()->default(null);

            // Status cancellation
            // null = pending, true = approved, false = rejected
            $table->boolean('canceled')->nullable()->default(null);

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');

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
        Schema::dropIfExists('forms');
    }
}
