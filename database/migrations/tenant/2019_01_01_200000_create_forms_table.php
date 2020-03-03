<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('branch_id')->index()->nullable();
            $table->datetime('date');
            $table->string('number')->nullable()->unique();
            $table->string('edited_number')->nullable();
            $table->text('edited_notes')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();
            $table->boolean('done')->default(false);

            // Increment
            $table->unsignedInteger('increment');
            $table->unsignedMediumInteger('increment_group');

            $table->unsignedInteger('formable_id')->index();
            $table->string('formable_type');

            // approval status
            // 0 = pending, 1 = approved, -1 = rejected
            // when form created
            $table->unsignedInteger('request_approval_to')->nullable()->index();
            // when approve / rejected
            $table->unsignedInteger('approval_by')->nullable()->index();
            $table->datetime('approval_at')->nullable();
            $table->text('approval_reason')->nullable();
            $table->tinyInteger('approval_status')->default(0);

            $table->foreign('request_approval_to')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('approval_by')->references('id')->on('users')->onDelete('restrict');

            // cancellation status
            // 0 = pending, 1 = approved, -1 = rejected
            // when request cancel
            $table->unsignedInteger('request_cancellation_to')->nullable()->index();
            $table->unsignedInteger('request_cancellation_by')->nullable()->index();
            $table->datetime('request_cancellation_at')->nullable();
            $table->text('request_cancellation_reason')->nullable();
            // when approve / rejected
            $table->datetime('cancellation_approval_at')->nullable();
            $table->unsignedInteger('cancellation_approval_by')->nullable()->index();
            $table->text('cancellation_approval_reason')->nullable();
            $table->tinyInteger('cancellation_status')->nullable();

            $table->foreign('request_cancellation_to')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('request_cancellation_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('cancellation_approval_by')->references('id')->on('users')->onDelete('restrict');

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forms');
    }
}
