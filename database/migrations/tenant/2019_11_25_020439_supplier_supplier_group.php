<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SupplierSupplierGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_supplier_group', function (Blueprint $table) {
            $table->unsignedInteger('supplier_id')->index();
            $table->unsignedInteger('supplier_group_id')->index();
            $table->timestamp('created_at');

            $table->unique(['supplier_id', 'supplier_group_id']);
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            $table->foreign('supplier_group_id')->references('id')->on('supplier_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_supplier_group');
    }
}
