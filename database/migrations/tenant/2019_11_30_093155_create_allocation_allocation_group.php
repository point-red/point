<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllocationAllocationGroup extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allocation_allocation_group', function (Blueprint $table) {
            $table->unsignedInteger('allocation_id')->index();
            $table->unsignedInteger('allocation_group_id')->index();
            $table->timestamp('created_at');

            $table->unique(['allocation_id', 'allocation_group_id'], 'allocation_allocation_group_allocation_and_group_id_unique');
            $table->foreign('allocation_id')->references('id')->on('allocations')->onDelete('cascade');
            $table->foreign('allocation_group_id')->references('id')->on('allocation_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allocation_allocation_group');
    }
}
