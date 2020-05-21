<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->nullable()->unique();
            $table->string('tax_identification_number')->nullable();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->decimal('credit_ceiling', 65, 30)->default(0);
            $table->unsignedInteger('branch_id')->index()->nullable();
            $table->unsignedInteger('created_by')->index()->nullable();
            $table->unsignedInteger('updated_by')->index()->nullable();
            $table->unsignedInteger('archived_by')->index()->nullable();
            $table->unsignedInteger('pricing_group_id')->nullable()->index();
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('pricing_group_id')->references('id')->on('pricing_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
