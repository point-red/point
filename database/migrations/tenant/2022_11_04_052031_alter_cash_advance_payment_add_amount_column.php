<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCashAdvancePaymentAddAmountColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cash_advance_payment', function (Blueprint $table) {
            $table->unsignedDecimal('amount', 65, 30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cash_advance_payment', function (Blueprint $table) {
            $table->dropColumn(['amount']);
        });
    }
}
