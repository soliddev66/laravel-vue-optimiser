<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdvertiserIdIntoRedtrackTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('redtrack_reports', function (Blueprint $table) {
            $table->string('advertiser_id')->after('open_id')->nullable();
        });
        Schema::table('redtrack_content_stats', function (Blueprint $table) {
            $table->string('advertiser_id')->after('open_id')->nullable();
        });
        Schema::table('redtrack_domain_stats', function (Blueprint $table) {
            $table->string('advertiser_id')->after('open_id')->nullable();
        });
        Schema::table('redtrack_publisher_stats', function (Blueprint $table) {
            $table->string('advertiser_id')->after('open_id')->nullable();
        });
        // Migrate data
        DB::statement('
            UPDATE redtrack_reports,( SELECT id,campaigns.advertiser_id FROM campaigns) campaign
            SET redtrack_reports.advertiser_id=campaign.advertiser_id
            WHERE redtrack_reports.campaign_id=campaign.id;
        ');
        DB::statement('
            UPDATE redtrack_content_stats,( SELECT id,campaigns.advertiser_id FROM campaigns) campaign
            SET redtrack_content_stats.advertiser_id=campaign.advertiser_id
            WHERE redtrack_content_stats.campaign_id=campaign.id;
        ');
        DB::statement('
            UPDATE redtrack_domain_stats,( SELECT id,campaigns.advertiser_id FROM campaigns) campaign
            SET redtrack_domain_stats.advertiser_id=campaign.advertiser_id
            WHERE redtrack_domain_stats.campaign_id=campaign.id;
        ');
        DB::statement('
            UPDATE redtrack_publisher_stats,( SELECT id,campaigns.advertiser_id FROM campaigns) campaign
            SET redtrack_publisher_stats.advertiser_id=campaign.advertiser_id
            WHERE redtrack_publisher_stats.campaign_id=campaign.id;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('redtrack_reports', function (Blueprint $table) {
            $table->dropColumn('advertiser_id');
        });
        Schema::table('redtrack_content_stats', function (Blueprint $table) {
            $table->dropColumn('advertiser_id');
        });
        Schema::table('redtrack_domain_stats', function (Blueprint $table) {
            $table->dropColumn('advertiser_id');
        });
        Schema::table('redtrack_publisher_stats', function (Blueprint $table) {
            $table->dropColumn('advertiser_id');
        });
    }
}
