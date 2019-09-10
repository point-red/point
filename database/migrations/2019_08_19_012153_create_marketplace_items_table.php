<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('item_id'); // reference from tenant items table
            $table->string('code')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('weight')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('taxable')->default(true);
            $table->unsignedInteger('unit_default')->nullable();
            $table->unsignedInteger('unit_default_purchase')->nullable();
            $table->unsignedInteger('unit_default_sales')->nullable();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketplace_items');
    }
}
