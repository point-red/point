<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOrderArchiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_order_archive', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_order_id')->index();
            $table->string('payment_type');
            $table->datetime('due_date')->nullable();
            $table->unsignedInteger('payment_account_id')->nullable();
            $table->decimal('amount', 65, 30);
            $table->unsignedInteger('paymentable_id')->nullable();
            $table->string('paymentable_type')->nullable();
            $table->string('paymentable_name')->nullable();
            $table->unsignedInteger('payment_id')->nullable();
            $table->unsignedInteger('form_id')->nullable()->index();
            $table->unsignedInteger('supplier_id')->index();
            $table->decimal('total_invoice', 65, 30);
            $table->decimal('total_down_payment', 65, 30);
            $table->decimal('total_return', 65, 30);
            $table->decimal('total_other', 65, 30);
            $table->timestamps();

            $table->foreign('payment_order_id')
                ->references('id')
                ->on('payment_orders');
            $table->foreign('payment_account_id')
                ->references('id')
                ->on('chart_of_accounts');
            $table->foreign('payment_id')
                ->references('id')
                ->on('payments');
            $table->foreign('form_id')
                ->references('id')
                ->on('forms');
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_order_archive');
    }
}
