<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveColumnIdToPsychologyKraeplinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('psychology_kraeplins', function (Blueprint $table) {
            $table->unsignedInteger('active_column_id')->index()->nullable();

            // Relationship
            $table->foreign('active_column_id')
                ->references('id')
                ->on('psychology_kraeplin_columns')
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
        Schema::table('psychology_kraeplin', function (Blueprint $table) {
            $table->dropColumn('active_column_id');
        });
    }
}
