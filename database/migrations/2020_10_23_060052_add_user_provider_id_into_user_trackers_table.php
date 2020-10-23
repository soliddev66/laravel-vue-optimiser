<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserProviderIdIntoUserTrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_trackers', function (Blueprint $table) {
            $table->integer('provider_id')->unsigned()->after('tracker_id');
            $table->string('provider_open_id')->after('provider_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_trackers', function (Blueprint $table) {
            $table->dropColumn('provider_id');
            $table->dropColumn('provider_open_id');
        });
    }
}
