<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOpenIdColumnToRedtrackDomainStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redtrack_domain_stats', function (Blueprint $table) {
            $table->string('open_id')->after('campaign_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redtrack_domain_stats', function (Blueprint $table) {
            $table->dropColumn('open_id');
        });
    }
}
