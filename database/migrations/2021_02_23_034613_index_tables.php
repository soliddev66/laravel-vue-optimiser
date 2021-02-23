<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Index campaigns table
        // https://drive.google.com/file/d/1-FXDX02Q1MSw9H2ryIWqbRR-dxW-7_Fd/view?usp=sharing
        DB::statement('CREATE INDEX campaigns_index_0 ON campaigns (provider_id)');
        DB::statement('CREATE INDEX campaigns_index_1 ON campaigns (open_id)');
        DB::statement('CREATE INDEX campaigns_index_2 ON campaigns (advertiser_id)');
        DB::statement('CREATE INDEX campaigns_index_3 ON campaigns (campaign_id)');

        // Index ad_groups table
        // https://drive.google.com/file/d/10l6CrMYjgIg4TcsXkVxGfWe4vaub3_D6/view?usp=sharing
        DB::statement('CREATE INDEX ad_groups_index_0 ON ad_groups (provider_id)');
        DB::statement('CREATE INDEX ad_groups_index_1 ON ad_groups (campaign_id)');
        DB::statement('CREATE INDEX ad_groups_index_2 ON ad_groups (open_id)');
        DB::statement('CREATE INDEX ad_groups_index_3 ON ad_groups (advertiser_id)');
        DB::statement('CREATE INDEX ad_groups_index_4 ON ad_groups (ad_group_id)');

        // Index ads table
        // https://drive.google.com/file/d/13kNw6WHX9fhWUwRm5wwPhUEwspAFyy-d/view?usp=sharing
        DB::statement('CREATE INDEX ads_index_0 ON ads (provider_id)');
        DB::statement('CREATE INDEX ads_index_1 ON ads (campaign_id)');
        DB::statement('CREATE INDEX ads_index_2 ON ads (open_id)');
        DB::statement('CREATE INDEX ads_index_3 ON ads (advertiser_id)');
        DB::statement('CREATE INDEX ads_index_4 ON ads (ad_group_id)');
        DB::statement('CREATE INDEX ads_index_5 ON ads (ad_id)');

        // Index redtrack_reports table
        // https://drive.google.com/file/d/17104vsmCLazgCv2Gy047co4Rt-oyFphQ/view?usp=sharing
        DB::statement('CREATE INDEX redtrack_reports_index_0 ON redtrack_reports (user_id)');
        DB::statement('CREATE INDEX redtrack_reports_index_1 ON redtrack_reports (provider_id)');
        DB::statement('CREATE INDEX redtrack_reports_index_2 ON redtrack_reports (open_id)');
        DB::statement('CREATE INDEX redtrack_reports_index_3 ON redtrack_reports (advertiser_id)');
        DB::statement('CREATE INDEX redtrack_reports_index_4 ON redtrack_reports (campaign_id)');
        DB::statement('CREATE INDEX redtrack_reports_index_5 ON redtrack_reports (sub3)');
        DB::statement('CREATE INDEX redtrack_reports_index_6 ON redtrack_reports (sub5)');
        DB::statement('CREATE INDEX redtrack_reports_index_7 ON redtrack_reports (sub6)');
        DB::statement('CREATE INDEX redtrack_reports_index_8 ON redtrack_reports (date)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
