<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('timezone')->default('UTC');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('vat_id_number')->nullable();
            $table->unsignedInteger('owner_id')->index();
            $table->string('invitation_code', 20)->nullable()->unique();
            $table->boolean('invitation_code_enabled')->default(false);
            $table->timestamps();

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
