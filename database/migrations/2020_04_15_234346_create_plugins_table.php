<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description');
            $table->unsignedDecimal('price', 65, 30);
            $table->boolean('is_monthly_price');
            $table->unsignedDecimal('price_per_user', 65, 30);
            $table->boolean('is_monthly_price_per_user');
            $table->unsignedInteger('user_id')->nullable();
            $table->boolean('is_active');
            // If the plugin host their own app they can put the link here
            // The link example https://domain.com
            $table->string('app_url')->nullable();
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
        Schema::dropIfExists('plugins');
    }
}
