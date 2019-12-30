<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCloudStoragesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cloud_storages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('owner_id')->nullable();
            $table->boolean('is_user_protected')->default(true);
            $table->unsignedInteger('project_id')->nullable();
            $table->string('feature');
            $table->unsignedInteger('feature_id')->nullable();
            $table->text('notes')->nullable();
            $table->string('file_name');
            $table->string('file_ext');
            $table->string('key');
            $table->string('path');
            $table->string('disk');
            $table->string('download_url');
            $table->datetime('expired_at');
            $table->timestamps();

            $table->foreign('owner_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
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
        Schema::dropIfExists('cloud_storages');
    }
}
