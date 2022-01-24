<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCutoffDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cutoff_id');
            $table->unsignedDecimal('debit', '65', 30);
            $table->unsignedDecimal('credit', '65', 30);
            $table->unsignedInteger('cutoffable_id')->index();
            $table->string('cutoffable_type');
            $table->string('cutoffable_name')->nullable();
            $table->timestamps();


            $table->foreign('cutoff_id')->references('id')->on('cutoffs_new')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_cutoff_detail');
    }
}
