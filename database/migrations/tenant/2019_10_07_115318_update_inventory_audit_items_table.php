<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInventoryAuditItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_audit_items', function (Blueprint $table) {
            $table->string('production_number')->after('item_id')->nullable()->unique();
            $table->date('expiry_date')->after('production_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_audit_items', function (Blueprint $table) {
            $table->dropColumn('production_number');
            $table->dropColumn('expiry_date');
        });
    }
}
