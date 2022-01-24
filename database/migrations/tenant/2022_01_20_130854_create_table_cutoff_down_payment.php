<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCutoffDownPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_down_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cutoff_detail_id');
            $table->enum('payment_type', ['RECEIVABLE', 'PAYABLE']);
            $table->unsignedInteger('cutoff_paymentable_id')->index();
            $table->string('cutoff_downpaymentable_type');
            $table->string('cutoff_downpaymentable_name');
            $table->unsignedDecimal('amout', '65', 30);
            $table->text('notes');
            $table->timestamps();

            $table->foreign('cutoff_detail_id')->references('id')->on('cutoff_details')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_cutoff_down_payment');
    }
}
