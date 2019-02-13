<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Many To Many Polymorphic Relations
        // https://laravel.com/docs/5.7/eloquent-relationships#many-to-many-polymorphic-relations
        Schema::create('groupables', function (Blueprint $table) {
            $table->unsignedInteger('group_id');
            $table->unsignedInteger('groupable_id');
            $table->string('groupable_type');
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
        Schema::dropIfExists('groupables');
    }
}
