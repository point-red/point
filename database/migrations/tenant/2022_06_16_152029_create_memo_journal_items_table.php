<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemoJournalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memo_journal_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('memo_journal_id')->index();
            $table->unsignedInteger('chart_of_account_id')->index();
            $table->string('chart_of_account_name');
            $table->unsignedInteger('form_id')->nullable()->index();
            $table->unsignedInteger('masterable_id')->nullable()->index();
            $table->string('masterable_type')->nullable();
            $table->unsignedDecimal('debit', '65', 30);
            $table->unsignedDecimal('credit', '65', 30);
            $table->string('notes')->nullable();

            $table->foreign('memo_journal_id')
                ->references('id')->on('memo_journals')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('form_id')
                ->references('id')->on('forms')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('chart_of_account_id')
                ->references('id')->on('chart_of_accounts')
                ->onUpdate('restrict')
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
        Schema::dropIfExists('memo_journal_items');
    }
}
