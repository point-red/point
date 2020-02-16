<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditCardRefundedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_card_refunded', function (Blueprint $table) {
            $table->increments('id');
            $table->string('xendit_id');
            $table->string('external_id');
            $table->string('user_id');
            $table->string('credit_card_charge_id');
            $table->string('status');
            $table->unsignedDecimal('amount', 65, 30);
            $table->unsignedDecimal('fee_refund_amount', 65, 30);
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
        Schema::dropIfExists('xendit_card_refunded');
    }
}
