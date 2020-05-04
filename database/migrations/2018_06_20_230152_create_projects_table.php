<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('total_user');
            $table->string('group')->nullable();
            $table->string('timezone')->default('UTC');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('website')->nullable();
            $table->text('marketplace_notes')->nullable();
            $table->string('vat_id_number')->nullable();
            $table->unsignedInteger('owner_id')->index();
            $table->string('invitation_code', 20)->nullable()->unique();
            $table->boolean('invitation_code_enabled')->default(false);
            $table->boolean('is_generated')->default(false);
            $table->unsignedInteger('package_id')->index();
            $table->datetime('expired_date');
            $table->timestamps();

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->onDelete('restrict');
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
