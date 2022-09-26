<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSuppliersAddBankColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('suppliers', function (Blueprint $table) {
            // when request close form
            $table->string('bank_branch');
            $table->string('bank_name');
            $table->string('bank_account_name');
            $table->string('bank_account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'bank_branch', 
                'bank_name', 
                'bank_account_name', 
                'bank_account_number', 
            ]);
        });
    }
}
