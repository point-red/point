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
            $table->unsignedInteger('chart_of_account_id');
            $table->enum('payment_type', ['RECEIVABLE', 'PAYABLE']);
            $table->unsignedInteger('cutoff_downpaymentable_id')->index();
            $table->string('cutoff_downpaymentable_type');
            $table->date('date');
            $table->unsignedDecimal('amount', '65', 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['chart_of_account_id', 'cutoff_downpaymentable_id', 'cutoff_downpaymentable_type'], 'data_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cutoff_down_payments');
    }
}
