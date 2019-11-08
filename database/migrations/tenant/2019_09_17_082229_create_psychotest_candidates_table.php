<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychotestCandidatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychotest_candidates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('phone');
            $table->string('password');
            
            // $table->set('level', ['Staff', 'Supervisor', 'Manager', 'Direktur']);
            $table->string('level')->nullable()->default('');
            $table->string('ktp_number')->nullable()->default('');
            $table->string('place_of_birth')->nullable()->default('');
            $table->string('date_of_birth')->nullable()->default('');
            // $table->set('sex', ['Laki-laki', 'Perempuan']);
            $table->string('sex')->nullable()->default('');
            // $table->set('religion', ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu', 'Lainnya']);
            $table->string('religion')->nullable()->default('');
            // $table->set('marital_status', ['Menikah', 'Belum Menikah']);
            $table->string('marital_status')->nullable()->default('');

            $table->unsignedInteger('position_id')->index();

            $table->boolean('is_password_used')->default(false);

            $table->boolean('is_kraepelin_started')->default(false);
            $table->boolean('is_kraepelin_finished')->default(false);
            
            $table->boolean('is_papikostick_started')->default(false);
            $table->integer('current_papikostick_index')->default(0);
            $table->boolean('is_papikostick_finished')->default(false);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));

            // Relationship
            $table->foreign('position_id')
                ->references('id')
                ->on('psychotest_candidate_positions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('psychotest_candidates');
    }
}
