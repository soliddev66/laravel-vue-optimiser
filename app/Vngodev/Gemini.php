<?php

namespace App\Vngodev;

use App\Jobs\PullGeminiReport;
use App\Models\Campaign;
use App\Models\GeminiJob;
use App\Models\UserProvider;
use App\Vngodev\Token;
use Carbon\Carbon;
use GuzzleHttp\Client;

/**
 * Gemini
 */
class Gemini
{
    public function __construct()
    {
        //
    }

    public static function crawl()
    {
        $date = Carbon::now()->subDay()->format('Y-m-d');
        Campaign::where('provider_id', 1)->whereNotIn('campaign_id', GeminiJob::where('status', '!=', 'completed')->groupBy('campaign_id')->pluck('campaign_id'))->chunk(10, function ($campaigns) use ($date) {
            foreach ($campaigns as $key => $campaign) {
                $jobs = [];
                $user_info = UserProvider::where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first();
                Token::refresh($user_info, function () use ($campaign, $user_info, $date, &$jobs) {
                    $job = self::getPerformanceDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'performance_stats';
                    array_push($jobs, $job);
                    $job = self::getSlotPerformanceDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'slot_performance_stats';
                    array_push($jobs, $job);
                    $job = self::getSitePerformanceDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'site_performance_stats';
                    array_push($jobs, $job);
                    $job = self::getCampaignBidPerformanceDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'campaign_bid_performance_stats';
                    array_push($jobs, $job);
                    $job = self::getStructuredSnippetExtensionPerformanceDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'structured_snippet_extension';
                    array_push($jobs, $job);
                    $job = self::getProductAdPerformanceDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'product_ad_performance_stats';
                    array_push($jobs, $job);
                    $job = self::getAdjustmentDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'adjustment_stats';
                    array_push($jobs, $job);
                    $job = self::getKeywordDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'keyword_stats';
                    array_push($jobs, $job);
                    $job = self::getSearchDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'search_stats';
                    array_push($jobs, $job);
                    $job = self::getAdExtensionDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'ad_extension_details';
                    array_push($jobs, $job);
                    $job = self::getCallExtensionDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'call_extension_stats';
                    array_push($jobs, $job);
                    // WON'T DO
                    // $job = self::getUserDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    // $job['name'] = 'user_stats';
                    // array_push($jobs, $job);
                    $job = self::getProductAdsDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'product_ads';
                    array_push($jobs, $job);
                    $job = self::getConversionRulesDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'conversion_rules_stats';
                    array_push($jobs, $job);
                    $job = self::getDomainPerformanceDataByCampaign($user_info, $date, $campaign->campaign_id, $campaign->advertiser_id);
                    $job['name'] = 'domain_performance_stats';
                    array_push($jobs, $job);
                });

                foreach ($jobs as $index => $job) {
                    GeminiJob::create([
                        'user_id' => $campaign->user_id,
                        'campaign_id' => $campaign->campaign_id,
                        'advertiser_id' => $campaign->advertiser_id,
                        'status' => 'submitted',
                        'name' => $job['name'],
                        'job_id' => $job['response']['jobId'],
                        'job_response' => $job['response']['jobResponse'],
                        'submited_at' => $job['timestamp']
                    ]);
                }
            }
        });
    }

    public static function checkJobs()
    {
        GeminiJob::where('status', '!=', 'completed')->chunk(10, function($jobs) {
            foreach ($jobs as $key => $job) {
                PullGeminiReport::dispatch($job)->onQueue('low2-queues');
            }
        });
    }

    private static function getPerformanceDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'performance_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Ad ID'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Hour'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Device Type'],
                    ['field' => 'Source Name'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'Conversions'],
                    ['field' => 'Total Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Average Position'],
                    ['field' => 'Max Bid'],
                    ['field' => 'Ad Extn Impressions'],
                    ['field' => 'Ad Extn Clicks'],
                    ['field' => 'Ad Extn Conversions'],
                    ['field' => 'Ad Extn Spend'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Average CPM'],
                    ['field' => 'CTR'],
                    ['field' => 'Video Starts'],
                    ['field' => 'Video Views'],
                    ['field' => 'Video 25% Complete'],
                    ['field' => 'Video 50% Complete'],
                    ['field' => 'Video 75% Complete'],
                    ['field' => 'Video 100% Complete'],
                    ['field' => 'Cost Per Video View'],
                    ['field' => 'Video Closed'],
                    ['field' => 'Video Skipped'],
                    ['field' => 'Video after 30 seconds view'],
                    ['field' => 'In App Post Click Convs'],
                    ['field' => 'In App Post View Convs'],
                    ['field' => 'In App Post Install Convs'],
                    ['field' => 'Opens'],
                    ['field' => 'Saves'],
                    ['field' => 'Save Rate'],
                    ['field' => 'Forwards'],
                    ['field' => 'Forward Rate'],
                    ['field' => 'Click Outs'],
                    ['field' => 'Click Outs Rate'],
                    ['field' => 'Fact Conversion Counting'],
                    ['field' => 'Interactions'],
                    ['field' => 'Interaction Rate'],
                    ['field' => 'Interactive Impressions Rate']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getSlotPerformanceDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'slot_performance_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Ad ID'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Hour'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Source'],
                    ['field' => 'Card ID'],
                    ['field' => 'Card Position'],
                    ['field' => 'Ad Format Name'],
                    ['field' => 'Rendered Type'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'CTR']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getSitePerformanceDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'site_performance_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Day'],
                    ['field' => 'External Site Name'],
                    ['field' => 'External Site Group Name'],
                    ['field' => 'Device Type'],
                    ['field' => 'Bid Modifier'],
                    ['field' => 'Average Bid'],
                    ['field' => 'Modified Bid'],
                    ['field' => 'Spend'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'Conversions'],
                    ['field' => 'CTR'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Average CPM'],
                    ['field' => 'Fact Conversion Counting']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getCampaignBidPerformanceDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'campaign_bid_performance_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Section ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Day'],
                    ['field' => 'Supply Type'],
                    ['field' => 'Group or Site'],
                    ['field' => 'Group'],
                    ['field' => 'Bid Modifier'],
                    ['field' => 'Average Bid'],
                    ['field' => 'Modified Bid'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'Conversions'],
                    ['field' => 'Cost'],
                    ['field' => 'CTR'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Fact Conversion Counting']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getStructuredSnippetExtensionPerformanceDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'structured_snippet_extension',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Ad ID'],
                    ['field' => 'Keyword ID'],
                    ['field' => 'Structured Snippet Extn ID'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Device Type'],
                    ['field' => 'Structured Snippet Text'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Source'],
                    ['field' => 'Destination URL'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Average Position'],
                    ['field' => 'Max Bid'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Average Cost-per-install'],
                    ['field' => 'Average Cost-per-action'],
                    ['field' => 'Average CPM'],
                    ['field' => 'CTR']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getProductAdPerformanceDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'product_ad_performance_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Product Ad ID'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Device Type'],
                    ['field' => 'Source Name'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'Conversions'],
                    ['field' => 'Total Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Average Position'],
                    ['field' => 'Max Bid'],
                    ['field' => 'Ad Extn Impressions'],
                    ['field' => 'Ad Extn Clicks'],
                    ['field' => 'Ad Extn Conversions'],
                    ['field' => 'Ad Extn Spend'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Average CPM'],
                    ['field' => 'CTR'],
                    ['field' => 'Video Starts'],
                    ['field' => 'Video Views'],
                    ['field' => 'Video 25% Complete'],
                    ['field' => 'Video 50% Complete'],
                    ['field' => 'Video 75% Complete'],
                    ['field' => 'Video 100% Complete'],
                    ['field' => 'Cost Per Video View'],
                    ['field' => 'Video Closed'],
                    ['field' => 'Video Skipped'],
                    ['field' => 'Video after 30 seconds view'],
                    ['field' => 'In App Post Click Convs'],
                    ['field' => 'In App Post View Convs'],
                    ['field' => 'In App Post Install Convs'],
                    ['field' => 'Opens'],
                    ['field' => 'Saves'],
                    ['field' => 'Save Rate'],
                    ['field' => 'Forwards'],
                    ['field' => 'Forward Rate'],
                    ['field' => 'Click Outs'],
                    ['field' => 'Click Outs Rate'],
                    ['field' => 'Fact Conversion Counting']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getAdjustmentDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'adjustment_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Day'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Source Name'],
                    ['field' => 'Is Adjustment'],
                    ['field' => 'Adjustment Type'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Average Position']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getKeywordDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'keyword_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Ad ID'],
                    ['field' => 'Keyword ID'],
                    ['field' => 'Destination URL'],
                    ['field' => 'Day'],
                    ['field' => 'Device Type'],
                    ['field' => 'Source Name'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'Conversions'],
                    ['field' => 'Total Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Average Position'],
                    ['field' => 'Max Bid'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Average CPM'],
                    ['field' => 'CTR']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getSearchDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'search_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Ad ID'],
                    ['field' => 'Keyword ID'],
                    ['field' => 'Delivered Match Type'],
                    ['field' => 'Search Term'],
                    ['field' => 'Device Type'],
                    ['field' => 'Destination URL'],
                    ['field' => 'Day'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Spend'],
                    ['field' => 'Conversions'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'Average Position'],
                    ['field' => 'Max Bid'],
                    ['field' => 'Average CPC'],
                    ['field' => 'CTR'],
                    ['field' => 'Impression Share'],
                    ['field' => 'Click Share'],
                    ['field' => 'Conversion Share']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getAdExtensionDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'ad_extension_details',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Ad ID'],
                    ['field' => 'Keyword ID'],
                    ['field' => 'Ad Extn ID'],
                    ['field' => 'Device Type'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Destination URL'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Average Position'],
                    ['field' => 'Max Bid'],
                    ['field' => 'Call Conversion'],
                    ['field' => 'Average CPC'],
                    ['field' => 'Average Cost-per-install'],
                    ['field' => 'Average CPM'],
                    ['field' => 'CTR'],
                    ['field' => 'Average Call Duration']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getCallExtensionDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'call_extension_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Caller Name'],
                    ['field' => 'Caller Area Code'],
                    ['field' => 'Caller Number'],
                    ['field' => 'Call Start Time'],
                    ['field' => 'Call End Time'],
                    ['field' => 'Call Status'],
                    ['field' => 'Call Duration']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getUserDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'user_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Audience ID'],
                    ['field' => 'Audience Name'],
                    ['field' => 'Audience Type'],
                    ['field' => 'Audience Status'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Day'],
                    ['field' => 'Pricing Type'],
                    ['field' => 'Source Name'],
                    ['field' => 'Gender'],
                    ['field' => 'Age'],
                    ['field' => 'Device Type'],
                    ['field' => 'Country'],
                    ['field' => 'State'],
                    ['field' => 'City'],
                    ['field' => 'Zip'],
                    ['field' => 'DMA WOEID'],
                    ['field' => 'City WOEID'],
                    ['field' => 'State WOEID'],
                    ['field' => 'Zip WOEID'],
                    ['field' => 'Country WOEID'],
                    ['field' => 'Location Type'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Post Impression Conversions'],
                    ['field' => 'Conversions'],
                    ['field' => 'Total Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Reblogs'],
                    ['field' => 'Reblog Rate'],
                    ['field' => 'Likes'],
                    ['field' => 'Like Rate'],
                    ['field' => 'Follows'],
                    ['field' => 'Follow Rate'],
                    ['field' => 'Engagements'],
                    ['field' => 'Paid Engagements'],
                    ['field' => 'Engagement Rate'],
                    ['field' => 'Paid Engagement Rate'],
                    ['field' => 'Video Starts'],
                    ['field' => 'Video Views'],
                    ['field' => 'Video 25% Complete'],
                    ['field' => 'Video 50% Complete'],
                    ['field' => 'Video 75% Complete'],
                    ['field' => 'Video 100% Complete'],
                    ['field' => 'Cost Per Video View'],
                    ['field' => 'Video Closed'],
                    ['field' => 'Video Skipped'],
                    ['field' => 'Video after 30 seconds view'],
                    ['field' => 'Ad Extn Impressions'],
                    ['field' => 'Ad Extn Clicks'],
                    ['field' => 'Ad Extn Spend'],
                    ['field' => 'Average Position'],
                    ['field' => 'Landing Page Type'],
                    ['field' => 'Fact Conversion Counting']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getProductAdsDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'product_ads',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Category ID'],
                    ['field' => 'Category Name'],
                    ['field' => 'Device'],
                    ['field' => 'Product Type'],
                    ['field' => 'Brand'],
                    ['field' => 'Product ID'],
                    ['field' => 'Product Name'],
                    ['field' => 'Custom Label 0'],
                    ['field' => 'Custom Label 1'],
                    ['field' => 'Custom Label 2'],
                    ['field' => 'Custom Label 3'],
                    ['field' => 'Custom Label 4'],
                    ['field' => 'Source'],
                    ['field' => 'Month'],
                    ['field' => 'Week'],
                    ['field' => 'Day'],
                    ['field' => 'Impressions'],
                    ['field' => 'Clicks'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Total Conversions'],
                    ['field' => 'Spend'],
                    ['field' => 'Average CPC'],
                    ['field' => 'CTR']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getConversionRulesDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'conversion_rules_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Rule ID'],
                    ['field' => 'Rule Name'],
                    ['field' => 'Category Name'],
                    ['field' => 'Conversion Device'],
                    ['field' => 'Keyword ID'],
                    ['field' => 'Keyword Value'],
                    ['field' => 'Source Name'],
                    ['field' => 'Price Type'],
                    ['field' => 'Day'],
                    ['field' => 'Post View Conversions'],
                    ['field' => 'Post Click Conversions'],
                    ['field' => 'Conversion Value'],
                    ['field' => 'Post Click Conversion Value'],
                    ['field' => 'Post View Conversion Value'],
                    ['field' => 'Conversions'],
                    ['field' => 'In App Post Click Convs'],
                    ['field' => 'In App Post View Convs'],
                    ['field' => 'In App Post Install Convs'],
                    ['field' => 'Landing Page Type'],
                    ['field' => 'Fact Conversion Counting']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    private static function getDomainPerformanceDataByCampaign($user_info, $date, $campaign_id, $advertiser_id)
    {
        $client = new Client();
        $response = $client->request('POST', env('BASE_URL') . '/v3/rest/reports/custom', [
            'body' => json_encode([
                'cube' => 'domain_performance_stats',
                'fields' => [
                    ['field' => 'Advertiser ID'],
                    ['field' => 'Campaign ID'],
                    ['field' => 'Ad Group ID'],
                    ['field' => 'Day'],
                    ['field' => 'Clicks'],
                    ['field' => 'Spend'],
                    ['field' => 'Impressions'],
                    ['field' => 'Top Domain'],
                    ['field' => 'Package Name']
                ],
                'filters' => [
                    ['field' => 'Advertiser ID', 'operator' => '=', 'value' => $advertiser_id],
                    ['field' => 'Campaign ID', 'operator' => '=', 'value' => $campaign_id],
                    ['field' => 'Day', 'operator' => 'between', 'from' => $date, 'to' => $date]
                ]
            ]),
            'headers' => [
                'Authorization' => 'Bearer ' . $user_info->token,
                'Content-Type' => 'application/json'
            ]
        ]);

        return json_decode($response->getBody(), true);
    }
}
