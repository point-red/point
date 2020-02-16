<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditFvaCreatedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_fva_created', function (Blueprint $table) {
            $table->increments('id');
            $table->string('xendit_id');
            $table->string('external_id');
            $table->string('owner_id');
            $table->string('bank_code');
            $table->string('account_number');
            $table->string('merchant_code');
            $table->string('name');
            $table->string('status');
            $table->boolean('is_closed');
            $table->boolean('is_single_use');
            $table->timestamp('expiration_date');
            $table->timestamp('created');
            $table->timestamp('updated');
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
        Schema::dropIfExists('xendit_fva_created');
    }
}
