<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveColumnIdToPsychotestKraepelinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('psychotest_kraepelins', function (Blueprint $table) {
            $table->unsignedInteger('active_column_id')->index()->nullable();

            // Relationship
            $table->foreign('active_column_id')
                ->references('id')
                ->on('psychotest_kraepelin_columns')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(Schema::hasColumn('psychotest_kraeplins', 'active_column_id')) {
            Schema::table('psychotest_kraepelin', function (Blueprint $table) {
                $table->dropColumn('active_column_id');
            });
        }
    }
}
