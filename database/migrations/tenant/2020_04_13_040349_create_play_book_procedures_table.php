<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayBookProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('play_book_procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procedure_id')->nullable();
            $table->string('code', 32)->nullable();
            $table->string('name', 300)->nullable();
            $table->longtext('purpose')->nullable();
            $table->longtext('content')->nullable();
            $table->longtext('note')->nullable();
            $table->boolean('status')->default(true);
            $table->enum('approval_action', ['store', 'update', 'destroy'])->nullable();
            $table->unsignedInteger('approval_request_by')->nullable();
            $table->datetime('approval_request_at')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->unsignedInteger('approval_request_to')->nullable();
            $table->longtext('approval_note')->nullable();
            $table->integer('procedure_pending_id')->nullable();
            $table->timestamps();

            $table->foreign('procedure_id')->references('id')->on('play_book_procedures')->onDelete('cascade');
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
        Schema::dropIfExists('play_book_procedures');
    }
}
