<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_user', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('user_name');
            $table->string('user_email');
            // After user invite other user inside a project, invited user need to confirm to join this project
            // false : user invited into this project
            // true : user confirm to join this project
            $table->boolean('joined')->default(false);
            // This column is used when user request join to the company using invitation code
            // otherwise it value will be null
            // so we can know where user that need approval to join to the company
            $table->timestamp('request_join_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
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
        Schema::dropIfExists('project_user');
    }
}
