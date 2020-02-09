<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCutOffSalesDownPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cut_off_sales_down_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cut_off_id')->index();
            $table->unsignedInteger('supplier_id')->index();
            $table->text('notes');
            $table->decimal('amount', 65, 30)->default(0);
            $table->timestamps();

            $table->foreign('cut_off_id')
                ->references('id')
                ->on('cut_offs')
                ->onDelete('cascade');

            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cut_off_sales_down_payments');
    }
}
