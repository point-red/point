<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCutoffAsset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedInteger('fixed_asset_id');
            $table->unsignedInteger('supplier_id');
            $table->string('location');
            $table->date('purchase_date');
            $table->unsignedDecimal('quantity', '65', 30);
            $table->unsignedDecimal('price', '65', 30);
            $table->unsignedDecimal('total', '65', 30);
            $table->unsignedDecimal('accumulation', '65', 30);
            $table->unsignedDecimal('book_value', '65', 30);
            $table->timestamps();

            $table->foreign('fixed_asset_id')->references('id')->on('fixed_assets')->onDelete('restrict');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            $table->index(['chart_of_account_id', 'fixed_asset_id'], 'data_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cutoff_assets');
    }
}
