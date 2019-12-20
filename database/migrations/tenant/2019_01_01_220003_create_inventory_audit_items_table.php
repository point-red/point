<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryAuditItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_audit_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('inventory_audit_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->unsignedDecimal('quantity', 65, 30);
            $table->datetime('expiry_date')->nullable();
            $table->string('production_number')->nullable();
            $table->unsignedDecimal('price', 65, 30);
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->string('notes')->nullable();

            $table->foreign('inventory_audit_id')
                ->references('id')->on('inventory_audits')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onUpdate('restrict')
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
        Schema::dropIfExists('inventory_audit_items');
    }
}
