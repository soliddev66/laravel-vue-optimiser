<?php

namespace App\Jobs;

use App\Models\GeminiAdExtensionStat;
use App\Models\GeminiAdjustmentStat;
use App\Models\GeminiCallExtensionStat;
use App\Models\GeminiCampaignBidPerformanceStat;
use App\Models\GeminiConversionRulesStat;
use App\Models\GeminiDomainPerformanceStat;
use App\Models\GeminiKeywordStat;
use App\Models\GeminiPerformanceStat;
use App\Models\GeminiProductAdPerformanceStat;
use App\Models\GeminiProductAdsStat;
use App\Models\GeminiSearchStat;
use App\Models\GeminiSitePerformanceStat;
use App\Models\GeminiSlotPerformanceStat;
use App\Models\GeminiStructuredSnippetExtensionPerformanceStat;
use App\Models\RedtrackReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class DeleteDuplicates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $limit = 1000;

        $total_1 = GeminiPerformanceStat::count();
        $offset_1 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_performance_stats rp1
                INNER JOIN (SELECT * FROM gemini_performance_stats LIMIT {$limit} OFFSET {$offset_1}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.ad_id = rp2.ad_id AND
                    rp1.month = rp2.month AND
                    rp1.week = rp2.week AND
                    rp1.day = rp2.day AND
                    rp1.hour = rp2.hour AND
                    rp1.pricing_type = rp2.pricing_type AND
                    rp1.device_type = rp2.device_type AND
                    rp1.source_name = rp2.source_name;
            ");
            $offset_1 += $limit;
        } while($offset_1 <= $total_1);

        $total_2 = GeminiSlotPerformanceStat::count();
        $offset_2 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_slot_performance_stats rp1
                INNER JOIN (SELECT * FROM gemini_slot_performance_stats LIMIT {$limit} OFFSET {$offset_2}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.ad_id = rp2.ad_id AND
                    rp1.month = rp2.month AND
                    rp1.week = rp2.week AND
                    rp1.day = rp2.day AND
                    rp1.hour = rp2.hour AND
                    rp1.pricing_type = rp2.pricing_type AND
                    rp1.source = rp2.source AND
                    rp1.card_id = rp2.card_id AND
                    rp1.card_position = rp2.card_position AND
                    rp1.ad_format_name = rp2.ad_format_name AND
                    rp1.rendered_type = rp2.rendered_type;
            ");
            $offset_2 += $limit;
        } while($offset_2 <= $total_2);

        $total_3 = GeminiSitePerformanceStat::count();
        $offset_3 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_site_performance_stats rp1
                INNER JOIN (SELECT * FROM gemini_site_performance_stats LIMIT {$limit} OFFSET {$offset_3}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.day = rp2.day AND
                    rp1.external_site_name = rp2.external_site_name AND
                    rp1.external_site_group_name = rp2.external_site_group_name AND
                    rp1.device_type = rp2.device_type AND
                    rp1.bid_modifier = rp2.bid_modifier AND
                    rp1.average_bid = rp2.average_bid AND
                    rp1.modified_bid = rp2.modified_bid;
            ");
            $offset_3 += $limit;
        } while($offset_3 <= $total_3);

        $total_4 = GeminiCampaignBidPerformanceStat::count();
        $offset_4 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_campaign_bid_performance_stats rp1
                INNER JOIN (SELECT * FROM gemini_campaign_bid_performance_stats LIMIT {$limit} OFFSET {$offset_4}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.section_id = rp2.section_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.day = rp2.day AND
                    rp1.supply_type = rp2.supply_type AND
                    rp1.group_or_site = rp2.group_or_site AND
                    rp1.group = rp2.group AND
                    rp1.bid_modifier = rp2.bid_modifier AND
                    rp1.average_bid = rp2.average_bid AND
                    rp1.modified_bid = rp2.modified_bid;
            ");
            $offset_4 += $limit;
        } while($offset_4 <= $total_4);

        $total_5 = GeminiStructuredSnippetExtensionPerformanceStat::count();
        $offset_5 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_structured_snippet_extension_stats rp1
                INNER JOIN (SELECT * FROM gemini_structured_snippet_extension_stats LIMIT {$limit} OFFSET {$offset_5}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.ad_id = rp2.ad_id AND
                    rp1.keyword_id = rp2.keyword_id AND
                    rp1.structured_snippet_extn_id = rp2.structured_snippet_extn_id AND
                    rp1.month = rp2.month AND
                    rp1.week = rp2.week AND
                    rp1.day = rp2.day AND
                    rp1.pricing_type = rp2.pricing_type AND
                    rp1.source = rp2.source AND
                    rp1.destination_url = rp2.destination_url;
            ");
            $offset_5 += $limit;
        } while($offset_5 <= $total_5);

        $total_6 = GeminiProductAdPerformanceStat::count();
        $offset_6 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_product_ad_performance_stats rp1
                INNER JOIN (SELECT * FROM gemini_product_ad_performance_stats LIMIT {$limit} OFFSET {$offset_6}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.product_ad_id = rp2.product_ad_id AND
                    rp1.month = rp2.month AND
                    rp1.week = rp2.week AND
                    rp1.day = rp2.day AND
                    rp1.pricing_type = rp2.pricing_type AND
                    rp1.device_type = rp2.device_type AND
                    rp1.source_name = rp2.source_name;
            ");
            $offset_6 += $limit;
        } while($offset_6 <= $total_6);

        $total_7 = GeminiAdjustmentStat::count();
        $offset_7 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_adjustment_stats rp1
                INNER JOIN (SELECT * FROM gemini_adjustment_stats LIMIT {$limit} OFFSET {$offset_7}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.day = rp2.day AND
                    rp1.pricing_type = rp2.pricing_type AND
                    rp1.source_name = rp2.source_name AND
                    rp1.is_adjustment = rp2.is_adjustment AND
                    rp1.adjustment_type = rp2.adjustment_type;
            ");
            $offset_7 += $limit;
        } while($offset_7 <= $total_7);

        $total_8 = GeminiKeywordStat::count();
        $offset_8 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_keyword_stats rp1
                INNER JOIN (SELECT * FROM gemini_keyword_stats LIMIT {$limit} OFFSET {$offset_8}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.ad_id = rp2.ad_id AND
                    rp1.keyword_id = rp2.keyword_id AND
                    rp1.destination_url = rp2.destination_url AND
                    rp1.day = rp2.day AND
                    rp1.device_type = rp2.device_type AND
                    rp1.source_name = rp2.source_name;
            ");
            $offset_8 += $limit;
        } while($offset_8 <= $total_8);

        $total_9 = GeminiSearchStat::count();
        $offset_9 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_search_stats rp1
                INNER JOIN (SELECT * FROM gemini_search_stats LIMIT {$limit} OFFSET {$offset_9}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.ad_id = rp2.ad_id AND
                    rp1.keyword_id = rp2.keyword_id AND
                    rp1.delivered_match_type = rp2.delivered_match_type AND
                    rp1.search_term = rp2.search_term AND
                    rp1.device_type = rp2.device_type AND
                    rp1.destination_url = rp2.destination_url AND
                    rp1.day = rp2.day;
            ");
            $offset_9 += $limit;
        } while($offset_9 <= $total_9);

        $total_10 = GeminiAdExtensionStat::count();
        $offset_10 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_ad_extension_details rp1
                INNER JOIN (SELECT * FROM gemini_ad_extension_details LIMIT {$limit} OFFSET {$offset_10}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.ad_id = rp2.ad_id AND
                    rp1.keyword_id = rp2.keyword_id AND
                    rp1.ad_extn_id = rp2.ad_extn_id AND
                    rp1.device_type = rp2.device_type AND
                    rp1.month = rp2.month AND
                    rp1.week = rp2.week AND
                    rp1.day = rp2.day AND
                    rp1.pricing_type = rp2.pricing_type AND
                    rp1.destination_url = rp2.destination_url;
            ");
            $offset_10 += $limit;
        } while($offset_10 <= $total_10);

        $total_11 = GeminiCallExtensionStat::count();
        $offset_11 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_call_extension_stats rp1
                INNER JOIN (SELECT * FROM gemini_call_extension_stats LIMIT {$limit} OFFSET {$offset_11}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.month = rp2.month AND
                    rp1.week = rp2.week AND
                    rp1.day = rp2.day;
            ");
            $offset_11 += $limit;
        } while($offset_11 <= $total_11);

        $total_12 = GeminiProductAdsStat::count();
        $offset_12 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_product_ads rp1
                INNER JOIN (SELECT * FROM gemini_product_ads LIMIT {$limit} OFFSET {$offset_12}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.offer_id = rp2.offer_id AND
                    rp1.category_id = rp2.category_id AND
                    rp1.category_name = rp2.category_name AND
                    rp1.device = rp2.device AND
                    rp1.product_type = rp2.product_type AND
                    rp1.brand = rp2.brand AND
                    rp1.offer_group_id = rp2.offer_group_id AND
                    rp1.product_id = rp2.product_id AND
                    rp1.product_name = rp2.product_name AND
                    rp1.custom_label_0 = rp2.custom_label_0 AND
                    rp1.custom_label_1 = rp2.custom_label_1 AND
                    rp1.custom_label_2 = rp2.custom_label_2 AND
                    rp1.custom_label_3 = rp2.custom_label_3 AND
                    rp1.custom_label_4 = rp2.custom_label_4 AND
                    rp1.source = rp2.source AND
                    rp1.device_type = rp2.device_type AND
                    rp1.month = rp2.month AND
                    rp1.week = rp2.week AND
                    rp1.day = rp2.day;
            ");
            $offset_12 += $limit;
        } while($offset_12 <= $total_12);

        $total_13 = GeminiConversionRulesStat::count();
        $offset_13 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_conversion_rules_stats rp1
                INNER JOIN (SELECT * FROM gemini_conversion_rules_stats LIMIT {$limit} OFFSET {$offset_13}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.rule_id = rp2.rule_id AND
                    rp1.rule_name = rp2.rule_name AND
                    rp1.category_name = rp2.category_name AND
                    rp1.conversion_device = rp2.conversion_device AND
                    rp1.keyword_id = rp2.keyword_id AND
                    rp1.keyword_value = rp2.keyword_value AND
                    rp1.source_name = rp2.source_name AND
                    rp1.price_type = rp2.price_type AND
                    rp1.day = rp2.day;
            ");
            $offset_13 += $limit;
        } while($offset_13 <= $total_13);

        $total_14 = GeminiDomainPerformanceStat::count();
        $offset_14 = 0;
        do {
            DB::statement("
                DELETE rp1
                FROM gemini_domain_performance_stats rp1
                INNER JOIN (SELECT * FROM gemini_domain_performance_stats LIMIT {$limit} OFFSET {$offset_14}) rp2
                WHERE
                    rp1.id < rp2.id AND
                    rp1.advertiser_id = rp2.advertiser_id AND
                    rp1.campaign_id = rp2.campaign_id AND
                    rp1.ad_group_id = rp2.ad_group_id AND
                    rp1.top_domain = rp2.top_domain AND
                    rp1.package_name = rp2.package_name AND
                    rp1.day = rp2.day;
            ");
            $offset_14 += $limit;
        } while($offset_14 <= $total_14);
    }
}
