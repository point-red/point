<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->increments("id");
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->string('depreciation_method');
            $table->unsignedInteger('fixed_asset_group_id')->nullable();
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->unsignedInteger('accumulation_chart_of_account_id')->nullable();
            $table->unsignedInteger('depreciation_chart_of_account_id')->nullable();
            $table->unsignedInteger('useful_life_year')->nullable();
            $table->unsignedDecimal('salvage_value', '65', 30)->nullable()->default(0);

            $table->unsignedInteger('created_by')->index()->nullable();
            $table->unsignedInteger('updated_by')->index()->nullable();
            $table->unsignedInteger('archived_by')->index()->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('fixed_asset_group_id')->references('id')->on('fixed_asset_groups')->onDelete('restrict');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('accumulation_chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('depreciation_chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fixed_assets');
    }
}
