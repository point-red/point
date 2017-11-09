<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unsignedInteger('person_category_id')->index();
            $table->unsignedInteger('person_group_id')->nullable()->index();

            $table->unique(['name', 'person_categories_id']);

            $table->foreign('person_category_id')
                ->references('id')->on('person_categories')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('person_group_id')
                ->references('id')->on('person_groups')
                ->onUpdate('cascade')
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
        Schema::dropIfExists('persons');
    }
}
