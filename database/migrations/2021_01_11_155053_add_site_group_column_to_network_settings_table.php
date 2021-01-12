<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteGroupColumnToNetworkSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('network_settings', function (Blueprint $table) {
            $table->integer('user_id')->after('id')->unsigned();
            $table->text('site_group')->after('group_3b')->nullable();
        });

        Schema::dropIfExists('network_setting_groups');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('network_settings', function (Blueprint $table) {
            $table->dropColumn('user_id');
            $table->dropColumn('site_group');
        });
    }
}
