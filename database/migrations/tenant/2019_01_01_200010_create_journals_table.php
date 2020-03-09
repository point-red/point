<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id')->index();
            $table->unsignedInteger('form_id_reference')->nullable()->index();
            $table->unsignedInteger('chart_of_account_id')->index();
            $table->decimal('debit', 65, 30)->default(0);
            $table->decimal('credit', 65, 30)->default(0);
            $table->unsignedInteger('journalable_id')->index()->nullable();
            $table->string('journalable_type')->nullable();
            $table->unsignedInteger('sub_ledger_id')->index()->nullable();
            $table->string('sub_ledger_type')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_posted')->default(false);
            $table->timestamps();

            $table->foreign('chart_of_account_id')
                ->references('id')
                ->on('chart_of_accounts')
                ->onDelete('restrict');

            $table->foreign('form_id_reference')
                ->references('id')
                ->on('forms')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('form_id')
                ->references('id')
                ->on('forms')
                ->onUpdate('cascade')
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
        Schema::dropIfExists('journals');
    }
}
