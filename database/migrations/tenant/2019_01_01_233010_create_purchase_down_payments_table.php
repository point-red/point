<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseDownPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_down_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->string('supplier_name');
            $table->string('supplier_address')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->unsignedInteger('downpaymentable_id')->nullable()->index();
            $table->string('downpaymentable_type')->nullable()->index();
            $table->decimal('amount', 65, 30);
            $table->decimal('remaining', 65, 30);
            $table->unsignedInteger('paid_by')->nullable();

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            $table->foreign('paid_by')->references('id')->on('payments')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_down_payments');
    }
}
