<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditDisbursementSentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_disbursement_sent', function (Blueprint $table) {
            $table->increments('id');
            $table->string('xendit_id');
            $table->string('user_id');
            $table->string('external_id');
            $table->string('bank_code');
            $table->string('account_holder_name');
            $table->unsignedDecimal('amount', 65, 30);
            $table->string('transaction_id');
            $table->string('transaction_sequence');
            $table->string('disbursement_id');
            $table->string('disbursement_description');
            $table->string('failure_code');
            $table->boolean('is_instant');
            $table->string('status');
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
        Schema::dropIfExists('xendit_disbursement_sent');
    }
}
