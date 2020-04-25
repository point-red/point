<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditBatchDisbursementSentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_batch_disbursement_sent', function (Blueprint $table) {
            $table->increments('id');
            $table->string('xendit_id');
            $table->string('user_id');
            $table->string('er_id');
            $table->timestamp('approved_at');
            $table->unsignedInteger('total_disbursed_count');
            $table->unsignedDecimal('total_disbursed_amount', 65, 30);
            $table->unsignedInteger('total_error_count');
            $table->unsignedDecimal('total_error_amount', 65, 30);
            $table->unsignedInteger('total_upload_count');
            $table->unsignedDecimal('total_upload_amount', 65, 30);
            $table->string('reference');
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
        Schema::dropIfExists('xendit_batch_disbursement_sent');
    }
}
