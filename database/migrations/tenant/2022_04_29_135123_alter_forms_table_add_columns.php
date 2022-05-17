<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFormsTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->datetime('request_approval_at')->nullable();

            /**
             * close status
             * 0 = pending, 1 = approved, -1 = rejected
             *  */ 
            // when request close
            $table->unsignedInteger('request_close_to')->nullable()->index();
            $table->unsignedInteger('request_close_by')->nullable()->index();
            $table->datetime('request_close_at')->nullable();
            $table->text('request_close_reason')->nullable();
            // when close approve / rejected
            $table->datetime('close_approval_at')->nullable();
            $table->unsignedInteger('close_approval_by')->nullable()->index();
            $table->text('close_approval_reason')->nullable();
            $table->tinyInteger('close_status')->nullable();

            $table->foreign('request_close_to')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('request_close_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('close_approval_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropForeign([ 'request_close_to', 'request_close_by', 'close_approval_by' ]);
            $table->dropColumn([
                'request_approval_at',
                'request_close_to',
                'request_close_by',
                'request_close_at',
                'request_close_reason',
                'close_approval_at',
                'close_approval_by',
                'close_approval_reason',
                'close_status'
            ]);            
        });
    }
}
