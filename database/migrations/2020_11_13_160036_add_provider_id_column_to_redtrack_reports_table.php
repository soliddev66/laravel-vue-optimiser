<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProviderIdColumnToRedtrackReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redtrack_reports', function (Blueprint $table) {
            $table->integer('provider_id')->after('campaign_id')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redtrack_reports', function (Blueprint $table) {
            $table->dropColumn('provider_id');
        });
    }
}
