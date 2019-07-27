<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePosBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_bills', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->unsignedDecimal('discount_percent', 33, 30)->nullable();
            $table->unsignedDecimal('discount_value', 65, 30)->default(0);
            $table->string('type_of_tax'); // include / exclude / non
            $table->decimal('tax', 65, 30);
            $table->decimal('amount', 65, 30);
            $table->decimal('paid', 65, 30);
            $table->text('notes')->nullable();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pos_bills');
    }
}
