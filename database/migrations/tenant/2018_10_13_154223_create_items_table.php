<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->string('code')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('weight')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('taxable')->default(true);
            $table->boolean('disabled')->default(false);
            $table->unsignedDecimal('stock', '65', 30)->default(0);
            $table->unsignedDecimal('stock_reminder', '65', 30)->default(0);
            $table->unsignedInteger('unit_default')->nullable();
            $table->unsignedInteger('unit_default_purchase')->nullable();
            $table->unsignedInteger('unit_default_sales')->nullable();

            $table->unsignedInteger('created_by')->index()->nullable();
            $table->unsignedInteger('updated_by')->index()->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
