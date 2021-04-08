<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_providers', function($table) {
            $table->string('open_id', 50)->change();
        });

        Schema::table('campaigns', function($table) {
            $table->string('open_id', 50)->change();
            $table->string('campaign_id', 50)->change();
            $table->string('advertiser_id', 50)->change();

            $table->unique(['open_id', 'campaign_id', 'provider_id', 'user_id', 'advertiser_id'], 'campaigns__unique_index');
        });

        Schema::table('ad_groups', function($table) {
            $table->string('open_id', 50)->change();
            $table->string('campaign_id', 50)->change();
            $table->string('advertiser_id', 50)->change();
            $table->string('ad_group_id', 50)->change();

            $table->unique(['user_id', 'provider_id', 'open_id', 'campaign_id', 'ad_group_id', 'advertiser_id'], 'ad_groups__unique_index');
        });

        Schema::table('ads', function($table) {
            $table->string('open_id', 50)->change();
            $table->string('campaign_id', 50)->change();
            $table->string('advertiser_id', 50)->change();
            $table->string('ad_id', 50)->change();
            $table->string('ad_group_id', 50)->change();

            $table->unique(['ad_id', 'user_id', 'provider_id', 'open_id', 'campaign_id', 'ad_group_id', 'advertiser_id'], 'ads__unique_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_providers', function($table) {
            $table->string('open_id')->change();
        });

        Schema::table('campaigns', function($table) { // Need to remove the unique key first on an independency transaction, cannot combine with bellow command
            $table->dropUnique('campaigns__unique_index');
        });

        Schema::table('campaigns', function($table) {
            $table->string('open_id')->change();
            $table->string('campaign_id')->change();
        });

        Schema::table('ad_groups', function($table) {
            $table->dropUnique('ad_groups__unique_index');
        });

        Schema::table('ad_groups', function($table) {
            $table->string('open_id')->change();
            $table->string('campaign_id')->change();
            $table->string('advertiser_id')->change();
            $table->string('ad_group_id')->change();
        });

        Schema::table('ads', function($table) {
            $table->dropUnique('ads__unique_index');
        });

        Schema::table('ads', function($table) {
            $table->string('open_id')->change();
            $table->string('campaign_id')->change();
            $table->string('advertiser_id')->change();
            $table->string('ad_id')->change();
            $table->string('ad_group_id')->change();
        });
    }
}
