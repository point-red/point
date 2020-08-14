<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOauthTokenDeviceInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->boolean('is_mobile')->nullable();
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('mobile_vendor')->nullable();
            $table->string('mobile_model')->nullable();
            $table->string('engine_name')->nullable();
            $table->string('engine_version')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'is_mobile',
                'os_name',
                'os_version',
                'browser_name',
                'browser_version',
                'mobile_vendor',
                'mobile_model',
                'engine_name',
                'engine_version',
            ]);
        });
    }
}
