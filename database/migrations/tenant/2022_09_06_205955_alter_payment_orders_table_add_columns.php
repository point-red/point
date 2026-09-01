<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPaymentOrdersTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->unsignedInteger('form_id')->nullable()->index();
            $table->unsignedInteger('supplier_id')->index();
            $table->decimal('total_invoice', 65, 30);
            $table->decimal('total_down_payment', 65, 30);
            $table->decimal('total_return', 65, 30);
            $table->decimal('total_other', 65, 30);
            $table->timestamps();

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
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['form_id']);
            $table->dropColumn(['updated_at', 'created_at', 'total_other', 'total_return', 'total_down_payment', 'total_invoice', 'supplier_id', 'form_id']);
        });
    }
}
