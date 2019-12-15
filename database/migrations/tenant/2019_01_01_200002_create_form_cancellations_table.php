<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormCancellationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_cancellations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->timestamp('requested_at')->useCurrent();
            $table->datetime('expired_at');
            $table->datetime('approval_at')->nullable();
            $table->boolean('approved')->nullable();
            $table->string('token');
            $table->string('reason')->nullable();
            $table->unsignedInteger('requested_by');
            $table->unsignedInteger('requested_to');

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('requested_to')->references('id')->on('users')->onDelete('restrict');

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
        Schema::dropIfExists('form_cancellations');
    }
}
