<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCutoffPayment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('chart_of_account_id');
            $table->enum('payment_type', ['RECEIVABLE', 'PAYABLE']);
            $table->unsignedInteger('cutoff_paymentable_id')->index();
            $table->string('cutoff_paymentable_type');
            $table->date('date');
            $table->unsignedDecimal('amount', '65', 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['chart_of_account_id', 'cutoff_paymentable_id', 'cutoff_paymentable_type'], 'data_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cutoff_payments');
    }
}
