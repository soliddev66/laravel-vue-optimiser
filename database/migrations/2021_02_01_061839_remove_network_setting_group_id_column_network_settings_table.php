<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveNetworkSettingGroupIdColumnNetworkSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('network_settings', function (Blueprint $table) {
            $table->dropColumn('network_setting_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('network_settings', function (Blueprint $table) {
            $table->string('network_setting_group_id');
        });
    }
}
