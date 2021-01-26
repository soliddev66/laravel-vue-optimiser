<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYahooJapanReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('yahoo_japan_reports', function (Blueprint $table) {
            $table->id();

            $table->integer('campaign_id')->unsigned();
            $table->date('date')->nullable();

            $table->double('imps')->nullable();
            $table->double('imps_prev')->nullable();
            $table->double('click_cnt')->nullable();
            $table->double('click_rate')->nullable();
            $table->double('click_rate_prev')->nullable();
            $table->double('cost')->nullable();
            $table->double('avg_cpc')->nullable();
            $table->double('conversions')->nullable();
            $table->double('conversion_rate')->nullable();
            $table->double('conversions_via_ad_click')->nullable();
            $table->double('conversion_rate_via_ad_click')->nullable();
            $table->double('all_conversions')->nullable();
            $table->double('all_conversion_rate')->nullable();
            $table->double('cpa')->nullable();
            $table->double('conversion_value')->nullable();
            $table->double('value_per_conversions')->nullable();
            $table->double('conv_value_per_cost')->nullable();
            $table->double('all_conv_value_per_cost')->nullable();
            $table->double('conv_value_via_ad_click_per_cost')->nullable();
            $table->double('all_conversion_value')->nullable();
            $table->double('value_per_all_conversions')->nullable();
            $table->double('conversion_value_via_ad_click')->nullable();
            $table->double('value_per_conversions_via_ad_click')->nullable();
            $table->double('cpa_via_ad_click')->nullable();
            $table->double('all_cpa')->nullable();
            $table->double('cross_device_conversions')->nullable();
            $table->double('avg_deliver_rank')->nullable();
            $table->double('measured_imps')->nullable();
            $table->double('total_vimps')->nullable();
            $table->double('measured_imps_rate')->nullable();
            $table->double('vimps')->nullable();
            $table->double('viewable_imps_rate')->nullable();
            $table->double('in_view_rate')->nullable();
            $table->double('viewable_clicks')->nullable();
            $table->double('in_view_click_cnt')->nullable();
            $table->double('viewable_click_rate')->nullable();
            $table->double('in_view_click_rate')->nullable();
            $table->double('paid_video_views')->nullable();
            $table->double('paid_video_view_rate')->nullable();
            $table->double('average_cpv')->nullable();
            $table->double('video_views')->nullable();
            $table->double('video_views_to25')->nullable();
            $table->double('video_views_to50')->nullable();
            $table->double('video_views_to75')->nullable();
            $table->double('video_views_to95')->nullable();
            $table->double('video_views_to100')->nullable();
            $table->double('video_views_to3_sec')->nullable();
            $table->double('video_views_to10_sec')->nullable();
            $table->double('average_rate_video_viewed')->nullable();
            $table->double('average_duration_video_viewed')->nullable();
            $table->double('impression_share')->nullable();
            $table->double('budget_impression_share_lost_rate')->nullable();
            $table->double('rank_impression_share_lost_rate')->nullable();
            $table->double('view_through_conversions')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yahoo_japan_reports');
    }
}
