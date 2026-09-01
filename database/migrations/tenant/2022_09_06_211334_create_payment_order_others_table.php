<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOrderOthersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_order_others', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_order_id')->index();
            $table->unsignedInteger('chart_of_account_id')->index();
            $table->unsignedInteger('allocation_id')->nullable()->index();
            $table->decimal('amount', 65, 30);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('payment_order_id')
                ->references('id')
                ->on('payment_orders');
            $table->foreign('chart_of_account_id')
                ->references('id')
                ->on('chart_of_accounts');
            $table->foreign('allocation_id')
                ->references('id')
                ->on('allocations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_order_others');
    }
}
