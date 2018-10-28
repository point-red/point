<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMasterHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('historyable_id');
            $table->string('historyable_type');
            $table->string('column_name');
            $table->text('old');
            $table->text('new');
            $table->timestamp('updated_at')->useCurrent();
            $table->unsignedInteger('updated_by')->index()->nullable();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('master_histories');
    }
}
