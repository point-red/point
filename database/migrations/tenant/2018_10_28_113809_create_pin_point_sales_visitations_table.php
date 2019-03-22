<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinPointSalesVisitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_point_sales_visitations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('customer_id');
            $table->string('name');
            $table->string('group');
            $table->string('address');
            $table->string('phone');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_method'); // cash or credit
            $table->datetime('due_date')->nullable();
            $table->decimal('payment_received', 65, 30)->nullable();
            $table->boolean('is_repeat_order')->default(false);
            $table->timestamps();

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
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
        Schema::dropIfExists('pin_point_sales_visitations');
    }
}
