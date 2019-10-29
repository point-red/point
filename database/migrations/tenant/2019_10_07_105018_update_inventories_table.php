<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedInteger('item_unit_id')->index()->after('item_id');
            $table->string('production_number')->after('item_unit_id')->nullable();
            $table->date('expiry_date')->after('production_number')->nullable();

            $table->foreign('item_unit_id')
                ->references('id')
                ->on('item_units')
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
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn('item_unit_id');
            $table->dropColumn('production_number');
            $table->dropColumn('expiry_date');
        });
    }
}
