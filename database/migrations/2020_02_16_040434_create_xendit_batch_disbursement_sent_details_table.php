<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditBatchDisbursementSentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_batch_disbursement_sent_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('xendit_batch_disbursement_sent_id');
            $table->string('xendit_id');
            $table->string('external_id');
            $table->unsignedDecimal('amount', 65, 30);
            $table->string('valid_name');
            $table->string('description');
            $table->string('status');
            $table->string('bank_code');
            $table->string('bank_reference');
            $table->string('bank_account_number');
            $table->string('bank_account_name');
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
        Schema::dropIfExists('xendit_batch_disbursement_sent_details');
    }
}
