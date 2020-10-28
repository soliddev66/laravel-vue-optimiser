<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeminiReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gemini_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->float('hour')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('device_type')->nullable();
            $table->string('source_name')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('post_impression_conversions')->nullable();
            $table->float('conversions')->nullable();
            $table->float('total_conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('average_position')->nullable();
            $table->float('max_bid')->nullable();
            $table->float('ad_extn_impressions')->nullable();
            $table->float('ad_extn_clicks')->nullable();
            $table->float('ad_extn_conversions')->nullable();
            $table->float('ad_extn_spend')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('average_cpm')->nullable();
            $table->float('ctr')->nullable();
            $table->float('video_starts')->nullable();
            $table->float('video_views')->nullable();
            $table->float('video_25_complete')->nullable();
            $table->float('video_50_complete')->nullable();
            $table->float('video_75_complete')->nullable();
            $table->float('video_100_complete')->nullable();
            $table->float('cost_per_video_view')->nullable();
            $table->float('video_closed')->nullable();
            $table->float('video_skipped')->nullable();
            $table->float('video_after_30_seconds_view')->nullable();
            $table->float('in_app_post_click_convs')->nullable();
            $table->float('in_app_post_view_convs')->nullable();
            $table->float('in_app_post_install_convs')->nullable();
            $table->float('opens')->nullable();
            $table->float('saves')->nullable();
            $table->float('save_rate')->nullable();
            $table->float('forwards')->nullable();
            $table->float('forward_rate')->nullable();
            $table->float('click_outs')->nullable();
            $table->float('click_outs_rate')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->string('interactions')->nullable();
            $table->string('interaction_rate')->nullable();
            $table->float('interactive_impressions_rate')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_slot_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->float('hour')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source')->nullable();
            $table->float('card_id')->nullable();
            $table->float('card_position')->nullable();
            $table->string('ad_format_name')->nullable();
            $table->string('rendered_type')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('ctr')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_site_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->string('external_site_name')->nullable();
            $table->string('external_site_group_name')->nullable();
            $table->string('device_type')->nullable();
            $table->string('bid_modifier')->nullable();
            $table->float('average_bid')->nullable();
            $table->float('modified_bid')->nullable();
            $table->float('spend')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('post_impression_conversions')->nullable();
            $table->float('conversions')->nullable();
            $table->float('ctr')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('average_cpm')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_campaign_bid_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('section_id');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->string('supply_type')->nullable();
            $table->string('group_or_site')->nullable();
            $table->string('group')->nullable();
            $table->string('bid_modifier')->nullable();
            $table->float('average_bid')->nullable();
            $table->float('modified_bid')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('post_impression_conversions')->nullable();
            $table->float('conversions')->nullable();
            $table->float('cost')->nullable();
            $table->float('ctr')->nullable();
            $table->float('average_cpc')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_structured_snippet_extension_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->bigInteger('structured_snippet_extn_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source')->nullable();
            $table->string('destination_url')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('average_position')->nullable();
            $table->float('max_bid')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('average_cost_per_action')->nullable();
            $table->float('average_cpm')->nullable();
            $table->float('ctr')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_product_ad_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('product_ad_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->date('hour')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('device_type')->nullable();
            $table->string('source_name')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('post_impression_conversions')->nullable();
            $table->float('conversions')->nullable();
            $table->float('total_conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('average_position')->nullable();
            $table->float('max_bid')->nullable();
            $table->float('ad_extn_impressions')->nullable();
            $table->float('ad_extn_clicks')->nullable();
            $table->float('ad_extn_conversions')->nullable();
            $table->float('ad_extn_spend')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('average_cpm')->nullable();
            $table->float('ctr')->nullable();
            $table->float('video_starts')->nullable();
            $table->float('video_views')->nullable();
            $table->float('video_25_complete')->nullable();
            $table->float('video_50_complete')->nullable();
            $table->float('video_75_complete')->nullable();
            $table->float('video_100_complete')->nullable();
            $table->float('cost_per_video_view')->nullable();
            $table->float('video_closed')->nullable();
            $table->float('video_skipped')->nullable();
            $table->float('video_after_30_seconds_view')->nullable();
            $table->float('in_app_post_click_convs')->nullable();
            $table->float('in_app_post_view_convs')->nullable();
            $table->float('in_app_post_install_convs')->nullable();
            $table->float('opens')->nullable();
            $table->float('saves')->nullable();
            $table->float('save_rate')->nullable();
            $table->float('forwards')->nullable();
            $table->float('forward_rate')->nullable();
            $table->float('click_outs')->nullable();
            $table->float('click_out_rate')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_adjustment_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source_name')->nullable();
            $table->string('is_adjustment')->nullable();
            $table->string('adjustment_type')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('average_position')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_keyword_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->string('destination_url')->nullable();
            $table->date('day')->nullable();
            $table->string('device_type')->nullable();
            $table->string('source_name')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('post_impression_conversions')->nullable();
            $table->float('conversions')->nullable();
            $table->float('total_conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('average_position')->nullable();
            $table->float('max_bid')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('average_cpm')->nullable();
            $table->float('ctr')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_search_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->string('delivered_match_type')->nullable();
            $table->string('search_term')->nullable();
            $table->string('device_type')->nullable();
            $table->string('destination_url')->nullable();
            $table->date('day')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('spend')->nullable();
            $table->float('conversions')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('post_impression_conversions')->nullable();
            $table->float('average_position')->nullable();
            $table->float('max_bid')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('ctr')->nullable();
            $table->float('impression_share')->nullable();
            $table->float('click_share')->nullable();
            $table->float('conversion_share')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_ad_extension_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('ad_id');
            $table->bigInteger('keyword_id');
            $table->bigInteger('ad_extn_id');
            $table->string('device_type')->nullable();
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('destination_url')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('average_position')->nullable();
            $table->float('max_bid')->nullable();
            $table->float('call_conversion')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('average_cost_per_install')->nullable();
            $table->float('average_cpm')->nullable();
            $table->float('ctr')->nullable();
            $table->float('average_call_duration')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_call_extension_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->string('caller_name')->nullable();
            $table->string('caller_area_code')->nullable();
            $table->string('caller_number')->nullable();
            $table->string('call_start_time')->nullable();
            $table->string('call_end_time')->nullable();
            $table->string('call_status')->nullable();
            $table->float('call_duration')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_user_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('audience_id');
            $table->string('audience_name');
            $table->string('audience_type');
            $table->string('audience_status');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->string('pricing_type')->nullable();
            $table->string('source_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('age')->nullable();
            $table->string('device_type')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('dma_woeid')->nullable();
            $table->string('city_woeid')->nullable();
            $table->string('state_woeid')->nullable();
            $table->string('zip_woeid')->nullable();
            $table->string('country_woeid')->nullable();
            $table->string('location_type')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('post_impression_conversions')->nullable();
            $table->float('conversions')->nullable();
            $table->float('total_conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('reblogs')->nullable();
            $table->float('reblog_rate')->nullable();
            $table->float('likes')->nullable();
            $table->float('like_rate')->nullable();
            $table->float('follows')->nullable();
            $table->float('follow_rate')->nullable();
            $table->float('engagements')->nullable();
            $table->float('paid_engagements')->nullable();
            $table->float('engagement_rate')->nullable();
            $table->float('paid_engagement_rate')->nullable();
            $table->float('video_starts')->nullable();
            $table->float('video_views')->nullable();
            $table->float('video_25_complete')->nullable();
            $table->float('video_50_complete')->nullable();
            $table->float('video_75_complete')->nullable();
            $table->float('video_100_complete')->nullable();
            $table->float('cost_per_video_view')->nullable();
            $table->float('video_closed')->nullable();
            $table->float('video_skipped')->nullable();
            $table->float('video_after_30_seconds_view')->nullable();
            $table->float('ad_extn_impressions')->nullable();
            $table->float('ad_extn_clicks')->nullable();
            $table->float('ad_extn_spend')->nullable();
            $table->float('average_position')->nullable();
            $table->string('landing_page_type')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_product_ads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('offer_id');
            $table->bigInteger('category_id');
            $table->string('category_name');
            $table->string('device');
            $table->string('product_type');
            $table->string('brand');
            $table->bigInteger('offer_group_id');
            $table->bigInteger('product_id');
            $table->string('product_name');
            $table->string('custom_label_0');
            $table->string('custom_label_1');
            $table->string('custom_label_2');
            $table->string('custom_label_3');
            $table->string('custom_label_4');
            $table->string('source');
            $table->string('device_type');
            $table->date('month')->nullable();
            $table->date('week')->nullable();
            $table->date('day')->nullable();
            $table->float('impressions')->nullable();
            $table->float('clicks')->nullable();
            $table->float('post_view_conversions')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('total_conversions')->nullable();
            $table->float('spend')->nullable();
            $table->float('average_cpc')->nullable();
            $table->float('ctr')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_conversion_rules_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->bigInteger('rule_id');
            $table->string('rule_name');
            $table->string('category_name');
            $table->string('conversion_device');
            $table->bigInteger('keyword_id');
            $table->string('keyword_value');
            $table->string('source_name');
            $table->string('price_type');
            $table->date('day')->nullable();
            $table->float('post_view_conversions')->nullable();
            $table->float('post_click_conversions')->nullable();
            $table->float('conversion_value')->nullable();
            $table->float('post_view_conversion_value')->nullable();
            $table->float('conversions')->nullable();
            $table->float('in_app_post_click_convs')->nullable();
            $table->float('in_app_post_view_convs')->nullable();
            $table->float('in_app_post_install_convs')->nullable();
            $table->string('landing_page_type')->nullable();
            $table->string('fact_conversion_counting')->nullable();
            $table->timestamps();
        });

        Schema::create('gemini_domain_performance_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('advertiser_id');
            $table->integer('campaign_id');
            $table->bigInteger('ad_group_id');
            $table->date('day')->nullable();
            $table->float('clicks')->nullable();
            $table->float('spend')->nullable();
            $table->float('impressions')->nullable();
            $table->string('top_domain')->nullable();
            $table->string('package_name')->nullable();
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
        Schema::dropIfExists('gemini_performance_stats');
        Schema::dropIfExists('gemini_slot_performance_stats');
        Schema::dropIfExists('gemini_site_performance_stats');
        Schema::dropIfExists('gemini_campaign_bid_performance_stats');
        Schema::dropIfExists('gemini_structured_snippet_extension_stats');
        Schema::dropIfExists('gemini_product_ad_performance_stats');
        Schema::dropIfExists('gemini_adjustment_stats');
        Schema::dropIfExists('gemini_keyword_stats');
        Schema::dropIfExists('gemini_search_stats');
        Schema::dropIfExists('gemini_ad_extension_details');
        Schema::dropIfExists('gemini_call_extension_stats');
        Schema::dropIfExists('gemini_user_stats');
        Schema::dropIfExists('gemini_product_ads');
        Schema::dropIfExists('gemini_conversion_rules_stats');
        Schema::dropIfExists('gemini_domain_performance_stats');
    }
}
