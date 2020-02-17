<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date');
            $table->dateTime('due_date');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('project_id');
            $table->string('project_name')->nullable();
            $table->string('project_address')->nullable();
            $table->string('project_email')->nullable();
            $table->string('project_phone')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->unsignedDecimal('sub_total', 65, 30);
            $table->unsignedDecimal('discount_percent', 33, 30)->nullable();
            $table->unsignedDecimal('discount_value', 65, 30)->default(0);
            $table->unsignedDecimal('vat', 65, 30);
            $table->unsignedDecimal('total', 65, 30);
            $table->unsignedInteger('paidable_id')->nullable()->index();
            $table->string('paidable_type')->nullable()->index();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');

            $table->foreign('project_id')
                ->references('id')
                ->on('projects')
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
        Schema::dropIfExists('invoices');
    }
}
