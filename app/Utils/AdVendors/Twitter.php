<?php

namespace App\Utils\AdVendors;

use App\Endpoints\TwitterAPI;
use App\Jobs\DeleteAdGroup;
use App\Jobs\DeleteCampaign;
use App\Jobs\DeleteCard;
use App\Jobs\PullCampaign;
use App\Models\Ad;
use App\Models\AdGroup;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\RedtrackReport;
use App\Models\TwitterReport;
use App\Models\User;
use App\Models\UserProvider;
use App\Models\UserTracker;
use App\Vngodev\AdVendorInterface;
use App\Vngodev\Helper;
use App\Vngodev\ResourceImporter;
use Carbon\Carbon;
use DB;
use Exception;
use GuzzleHttp\Client;
use Hborras\TwitterAdsSDK\TwitterAdsException;

class Twitter extends Root implements AdVendorInterface
{
    use \App\Utils\AdVendors\Attributes\Twitter;

    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->orWhere('id', request('provider'))->first();

        return new TwitterAPI(auth()->user()->providers()->where('provider_id', $provider->id)->where('open_id', request('account'))->first(), request('advertiser') ?? null);
    }

    public function advertisers()
    {
        $advertisers = $this->api()->getAdvertisers();

        $result = [];

        foreach ($advertisers as $advertiser) {
            $result[] = [
                'id' => $advertiser->getId(),
                'name' => $advertiser->getName()
            ];
        }

        return $result;
    }

    public function countries()
    {
        return $this->api()->getCountries();
    }

    public function getCampaignInstance(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);

            $instance = $api->getCampaign($campaign->campaign_id)->toArray();

            $instance['provider'] = $campaign->provider->slug;
            $instance['open_id'] = $campaign['open_id'];
            $instance['advertiser_id'] = $campaign->advertiser_id;
            $instance['instance_id'] = $campaign['id'];

            $instance['adGroups'] = [];

            $instance['ads'] = [];

            $ad_groups = $api->getAdGroups($campaign->campaign_id);

            if ($ad_groups && count($ad_groups) > 0) {
                foreach ($ad_groups as $ad_group) {
                    $instance['adGroups'][] = $ad_group->toArray();
                }

                $promoted_tweets = $api->getPromotedTweets([$ad_groups[0]->getId()]);

                if ($promoted_tweets && count($promoted_tweets) > 0) {
                    $tweets = $api->getTweet($promoted_tweets[0]->getTweetId());

                    $instance['promoted_tweet_id'] = $promoted_tweets[0]->getId();

                    if ($tweets && count($tweets) > 0) {
                        foreach ($tweets as $tweet) {
                            $instance['ads'][] = $tweet->toArray();
                        }
                    }
                }
            }

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAdInstance(Campaign $campaign, $ad_group_id, $ad_id)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);

            $promoted_tweets = $api->getPromotedTweets([$ad_group_id]);

            if ($promoted_tweets && count($promoted_tweets) > 0) {
                $tweets = $api->getTweet($promoted_tweets[0]->getTweetId());

                if ($tweets && count($tweets) > 0) {
                    $instance = $tweets[0]->toArray();
                }
            }

            return $instance;
        } catch (Exception $e) {
            return [];
        }
    }

    public function cloneCampaignName(&$instance)
    {
        $instance['name'] = $instance['name'] . ' - Copy';
    }

    public function cloneAdName(&$instance)
    {
        $instance['full_text'] = $instance['full_text'] . ' - Copy';
    }

    public function fundingInstruments()
    {
        $funding_instruments = $this->api()->getFundingInstruments();

        $result = [];

        foreach ($funding_instruments as $funding_instrument) {
            $result[] = [
                'id' => $funding_instrument->getId(),
                'name' => $funding_instrument->getDescription()
            ];
        }

        return $result;
    }

    public function adGroupCategories()
    {
        return $this->api()->getAdGroupCategories();
    }

    public function store()
    {
        $api = $this->api();

        try {
            $promotable_users = $this->api()->getPromotableUsers();

            $campaign_data = $api->saveCampaign();
            $line_item_data = $api->saveLineItem($campaign_data);

            foreach (request('cards') as $card) {
                foreach ($card['media'] as $media_item) {
                    $media = $this->api()->uploadMedia($promotable_users, $media_item['media']);
                    $media_library = $this->api()->createMediaLibrary($media->media_key);

                    if ($card['type'] == 'IMAGE') {
                        $card_data = $api->createWebsiteCard($media->media_key, $card);
                    } else {
                        $card_data = $api->createVideoWebsiteCard($media->media_key, $card);
                    }

                    foreach ($card['tweetTexts'] as $tweet_text) {
                        $tweet_data = $api->createTweet($card_data, $promotable_users, $card, $tweet_text);
                        $promoted_tweet = $api->createPromotedTweet($line_item_data->getId(), $tweet_data);
                    }
                }
            }

            Helper::pullCampaign();
        } catch (Exception $e) {
            $this->rollback($campaign_data ?? null, $line_item_data ?? null, $card_data ?? null);
            if ($e instanceof TwitterAdsException && is_array($e->getErrors())) {
                return [
                    'errors' => [$e->getErrors()[0]->message]
                ];
            } else {
                return [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return [];
    }

    private function rollback($campaign_data = null, $line_item_data = null, $card_data = null)
    {
        if ($campaign_data) {
            DeleteCampaign::dispatch(auth()->user(), $campaign_data->getId(), request('provider'), request('account'), request('advertiser'))->onQueue('highest');
        }

        if ($line_item_data) {
            DeleteAdGroup::dispatch(auth()->user(), $line_item_data->getId(), request('provider'), request('account'), request('advertiser'))->onQueue('highest');
        }

        if ($card_data) {
            DeleteCard::dispatch(auth()->user(), $card_data->getId(), request('provider'), request('account'), request('advertiser'))->onQueue('highest');
        }
    }

    public function update(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);

            $campaign_data = $api->saveCampaign($campaign->campaign_id);
            $line_item_data = $api->saveLineItem($campaign_data, request('adGroupID'));

            if (!request('saveCard')) {
                $promotable_users = $this->api()->getPromotableUsers();
                $promoted_tweets = $api->getPromotedTweets([$line_item_data->getId()]);

                if ($promoted_tweets && count($promoted_tweets) > 0) {
                    foreach ($promoted_tweets as $promoted_tweet) {
                        $api->deletePromotedTweet($promoted_tweet->getId());
                    }
                }

                foreach (request('cards') as $card) {
                    foreach ($card['media'] as $media_item) {
                        $media = $this->api()->uploadMedia($promotable_users, $media_item['media']);
                        $media_library = $this->api()->createMediaLibrary($media->media_key);

                        if ($card['type'] == 'IMAGE') {
                            $card_data = $api->createWebsiteCard($media->media_key, $card);
                        } else {
                            $card_data = $api->createVideoWebsiteCard($media->media_key, $card);
                        }

                        foreach ($card['tweetTexts'] as $tweet_text) {
                            $tweet_data = $api->createTweet($card_data, $promotable_users, $card, $tweet_text);
                            $promoted_tweet = $api->createPromotedTweet($line_item_data->getId(), $tweet_data);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }

        return [];
    }

    public function storeAd(Campaign $campaign, $ad_group_id)
    {
        $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);

        $promotable_users = $api->getPromotableUsers();

        foreach (request('cards') as $card) {
            foreach ($card['media'] as $media_item) {
                $media = $api->uploadMedia($promotable_users, $media_item['media']);
                $media_library = $api->createMediaLibrary($media->media_key);

                if ($card['type'] == 'IMAGE') {
                    $card_data = $api->createWebsiteCard($media->media_key, $card);
                } else {
                    $card_data = $api->createVideoWebsiteCard($media->media_key, $card);
                }

                foreach ($card['tweetTexts'] as $tweet_text) {
                    $tweet_data = $api->createTweet($card_data, $promotable_users, $card, $tweet_text);
                    $promoted_tweet = $api->createPromotedTweet($ad_group_id, $tweet_data);
                }
            }
        }

        return [];
    }

    public function delete(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);
            $api->deleteCampaign($campaign->campaign_id);
            $campaign->delete();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function status(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(UserProvider::where(['provider_id' => $campaign->provider_id, 'open_id' => $campaign->open_id])->first(), $campaign->advertiser_id);
            $campaign->status = $campaign->status == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

            $api->updateCampaignStatus($campaign);

            $ad_groups = $api->getAdGroups($campaign->campaign_id);

            if ($ad_groups && count($ad_groups) > 0) {
                foreach ($ad_groups as $ad_group) {
                    $api->updateAdGroupStatus($ad_group->getId(), $campaign->status);
                }
            }

            $campaign->save();

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function adGroupData(Campaign $campaign)
    {
        $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);
        $ad_group_datas = $api->getAdGroups($campaign->campaign_id);

        $ad_groups = [];
        $ad_group_ids = [];
        $ads = [];

        if ($ad_group_datas && count($ad_group_datas) > 0) {
            foreach ($ad_group_datas as $ad_group) {
                $ad_groups[] = [
                    'id' => $ad_group->getId(),
                    'adGroupName' => $ad_group->getName(),
                    'advertiserId' => $campaign->advertiser_id,
                    'campaignId' => $campaign->campaign_id,
                    'startDateStr' => $ad_group->getStartTime() ? $ad_group->getStartTime()->format('Y-m-d') : '',
                    'endDateStr' => $ad_group->getEndTime() ? $ad_group->getEndTime()->format('Y-m-d') : '',
                    'status' => $ad_group->getEntityStatus()
                ];

                $ad_group_ids[] = $ad_group->getId();
            }

            $promoted_tweets = $api->getPromotedTweets($ad_group_ids);

            if ($promoted_tweets && count($promoted_tweets)) {
                foreach ($promoted_tweets as $promoted_tweet) {
                    $ads[] = [
                        'id' => $promoted_tweet->getId(),
                        'title' => $promoted_tweet->getId(),
                        'advertiserId' => $campaign->advertiser_id,
                        'campaignId' => $campaign->campaign_id,
                        'adGroupId' => $promoted_tweet->getLineItemId()
                    ];
                }
            }
        }

        return response()->json([
            'ad_groups' => $ad_groups,
            'ads' => $ads,
            'summary_data' => new \stdClass()
        ]);
    }

    public function adGroupSelection(Campaign $campaign)
    {
        $api = new TwitterAPI(auth()->user()->providers()->where([
            'provider_id' => $campaign->provider_id,
            'open_id' => $campaign->open_id
        ])->first(), $campaign->advertiser_id);

        $ad_groups = $api->getAdGroups($campaign->campaign_id);

        $result = [];

        if (is_array($ad_groups) && count($ad_groups)) {
            foreach ($ad_groups as $ad_group) {
                $result[] = ['id' => $ad_group->getId(), 'text' => $ad_group->getName()];
            }
        }

        return $result;
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id, $status = null)
    {
        return [];
    }

    public function adGroupStatus(Campaign $campaign, $ad_group_id)
    {
        $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);
        $status = request('status') == Campaign::STATUS_ACTIVE ? Campaign::STATUS_PAUSED : Campaign::STATUS_ACTIVE;

        try {
            $api->updateAdGroupStatus($ad_group_id, $status);

            return [];
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function pullCampaign($user_provider)
    {
        $db_campaigns = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        foreach ($user_provider->advertisers as $advertiser) {
            $campaigns = (new TwitterAPI($user_provider, $advertiser))->getCampaigns();

            if (is_array($campaigns)) {
                foreach ($campaigns as $item) {
                    $db_campaigns[] = [
                        'campaign_id' => $item->getId(),
                        'provider_id' => $user_provider->provider_id,
                        'user_id' => $user_provider->user_id,
                        'open_id' => $user_provider->open_id,
                        'advertiser_id' => $advertiser,
                        'name' => $item->getName(),
                        'status' => $item->getEntityStatus(),
                        'budget' => $item->getTotalBudgetAmountLocalMicro() ? ($item->getTotalBudgetAmountLocalMicro() / 1000000) : ($item->getDailyBudgetAmountLocalMicro() / 1000000),
                        'updated_at' => $updated_at
                    ];
                }
            }
        }

        if (count($db_campaigns)) {
            $resource_importer->insertOrUpdate('campaigns', $db_campaigns, ['campaign_id', 'provider_id', 'user_id', 'open_id', 'advertiser_id']);

            Campaign::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function pullAdGroup($user_provider)
    {
        $db_ad_groups = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        Campaign::where('user_id', $user_provider->user_id)->where('provider_id', 3)->chunk(10, function ($campaigns) use ($resource_importer, $user_provider, &$db_ad_groups, $updated_at) {
            foreach ($campaigns as $campaign) {
                $ad_groups = (new TwitterAPI($user_provider, $campaign->advertiser_id))->getAdGroups($campaign->campaign_id);
                if (is_array($ad_groups) && count($ad_groups)) {
                    foreach ($ad_groups as $ad_group) {
                        $db_ad_groups[] = [
                            'ad_group_id' => $ad_group->getId(),
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $campaign->campaign_id,
                            'advertiser_id' => $campaign->advertiser_id,
                            'open_id' => $user_provider->open_id,
                            'name' => $ad_group->getName(),
                            'status' => $ad_group->getEntityStatus(),
                            'updated_at' => $updated_at
                        ];
                    }
                }
            }
        });

        if (count($db_ad_groups)) {
            $resource_importer->insertOrUpdate('ad_groups', $db_ad_groups, ['ad_group_id', 'user_id', 'provider_id', 'campaign_id', 'advertiser_id', 'open_id']);

            AdGroup::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function pullAd($user_provider)
    {
        $db_ads = [];

        $resource_importer = new ResourceImporter();

        $updated_at = Carbon::now();

        AdGroup::where('user_id', $user_provider->user_id)->where('provider_id', 3)->chunk(10, function ($ad_groups) use ($resource_importer, $user_provider, &$db_ads, $updated_at) {
            foreach ($ad_groups as $key => $ad_group) {
                $ads = (new TwitterAPI($user_provider, $ad_group->advertiser_id))->getPromotedTweets([$ad_group->ad_group_id]);

                if ($ads) {
                    foreach ($ads as $key => $ad) {
                        $db_ads[] = [
                            'ad_id' => $ad->getId(),
                            'user_id' => $user_provider->user_id,
                            'provider_id' => $user_provider->provider_id,
                            'campaign_id' => $ad_group->campaign_id,
                            'advertiser_id' => $ad_group->advertiser_id,
                            'ad_group_id' => $ad_group->ad_group_id,
                            'open_id' => $user_provider->open_id,
                            'name' => $ad->getTweetId(),
                            'status' => $ad->getEntityStatus(),
                            'updated_at' => $updated_at
                        ];
                    }
                }
            }
        });

        if (count($db_ads)) {
            $resource_importer->insertOrUpdate('ads', $db_ads, ['ad_id', 'user_id', 'provider_id', 'campaign_id', 'advertiser_id', 'ad_group_id', 'open_id']);
            Ad::where([
                'user_id' => $user_provider->user_id,
                'provider_id' => $user_provider->provider_id,
                'open_id' => $user_provider->open_id
            ])->where('updated_at', '<>', $updated_at)->delete();
        }
    }

    public function deleteCampaign(User $user, $campaign_id, $provider_slug, $account, $advertiser)
    {
        $provider = Provider::where('slug', $provider_slug)->first();
        (new TwitterAPI($user->providers()->where('provider_id', $provider->id)->where('open_id', $account)->first(), $advertiser))->deleteCampaign($campaign_id);
    }

    public function deleteAdGroup(User $user, $ad_group_id, $provider_slug, $account, $advertiser)
    {
        $provider = Provider::where('slug', $provider_slug)->first();
        (new TwitterAPI($user->providers()->where('provider_id', $provider->id)->where('open_id', $account)->first(), $advertiser))->deleteLineItem($ad_group_id);
    }

    public function deleteCard(User $user, $card_id, $provider_slug, $account, $advertiser)
    {
        $provider = Provider::where('slug', $provider_slug)->first();
        (new TwitterAPI($user->providers()->where('provider_id', $provider->id)->where('open_id', $account)->first(), $advertiser))->deleteCard($card_id);
    }

    public function pullRedTrack($user_provider, $target_date = null)
    {
        $tracker = UserTracker::where('provider_id', $user_provider->provider_id)->where('provider_open_id', $user_provider->open_id)->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            if ($target_date) {
                $date = $target_date;
            }
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=sub3,hour_of_day&sub9=Twitter&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);
            if (count($data)) {
                foreach ($data as $key => $value) {
                    $campaign_id = substr($value['sub3'], 1, -1);
                    $campaigns = Campaign::where('campaign_id', $campaign_id)->get();
                    foreach ($campaigns as $index => $campaign) {
                        $value['date'] = $date;
                        $value['user_id'] = $campaign->user_id;
                        $value['campaign_id'] = $campaign->id;
                        $value['provider_id'] = $campaign->provider_id;
                        $value['open_id'] = $campaign->open_id;
                        $value['advertiser_id'] = $campaign->advertiser_id;
                        $redtrack_report = RedtrackReport::firstOrNew([
                            'date' => $date,
                            'sub3' => $campaign->campaign_id,
                            'hour_of_day' => $value['hour_of_day']
                        ]);
                        foreach (array_keys($value) as $array_key) {
                            $redtrack_report->{$array_key} = $value[$array_key];
                        }
                        $redtrack_report->save();
                    }
                }
            }
        }
    }

    public function getSummaryDataQuery($data, $campaign = null)
    {
        $summary_data_query = TwitterReport::select(
            DB::raw('ROUND(SUM(JSON_EXTRACT(data, "$[0].metrics.billed_charge_local_micro[0]") / 1000000), 2) as total_cost'),
            DB::raw('"N/A" as total_revenue'),
            DB::raw('"N/A" as total_net'),
            DB::raw('"N/A" as avg_roi')
        );
        $summary_data_query->leftJoin('campaigns', function ($join) use ($data) {
            $join->on('campaigns.id', '=', 'twitter_reports.campaign_id');
            if ($data['provider']) {
                $join->where('campaigns.provider_id', $data['provider']);
            }
            if ($data['account']) {
                $join->where('campaigns.open_id', $data['account']);
            }
            if ($data['advertiser']) {
                $join->where('campaigns.advertiser_id', $data['advertiser']);
            }
        });
        $summary_data_query->whereBetween('end_time', [request('start'), request('end')]);

        return $summary_data_query;
    }

    public function getCampaignQuery($data)
    {
        $campaigns_query = Campaign::select([
            DB::raw('MAX(campaigns.id) AS id'),
            DB::raw('campaigns.campaign_id AS campaign_id'),
            DB::raw('MAX(campaigns.name) AS name'),
            DB::raw('MAX(campaigns.status) AS status'),
            DB::raw('MAX(campaigns.budget) AS budget'),
            DB::raw('SUM(JSON_EXTRACT(data, "$[0].metrics.impressions")) as impressions'),
            DB::raw('SUM(JSON_EXTRACT(data, "$[0].metrics.clicks")) as clicks'),
            DB::raw('ROUND(SUM(JSON_EXTRACT(data, "$[0].metrics.billed_charge_local_micro[0]") / 1000000), 2) as cost')
        ]);
        $campaigns_query->leftJoin('twitter_reports', function ($join) use ($data) {
            $join->on('twitter_reports.campaign_id', '=', 'campaigns.id')->whereBetween('twitter_reports.end_time', [$data['start'], $data['end']]);
        });
        if ($data['provider']) {
            $campaigns_query->where('campaigns.provider_id', $data['provider']);
        }
        if ($data['account']) {
            $campaigns_query->where('campaigns.open_id', $data['account']);
        }
        if ($data['advertiser']) {
            $campaigns_query->where('campaigns.advertiser_id', $data['advertiser']);
        }
        if ($data['search']) {
            $campaigns_query->where('name', 'LIKE', '%' . $data['search'] . '%');
        }
        $campaigns_query->whereIn('campaigns.id', Campaign::select(DB::raw('MAX(campaigns.id) AS id'))->groupBy('campaign_id'));
        $campaigns_query->groupBy('campaigns.id', 'campaigns.campaign_id');

        return $campaigns_query;
    }

    public function getWidgetQuery($campaign, $data)
    {
        $widgets_query = TwitterReport::select([
            '*',
            DB::raw('JSON_EXTRACT(data, "$.summary.conversionMetrics[*].name") as widget_id'),
            DB::raw('NULL as calc_cpc'),
            DB::raw('NULL as tr_conv'),
            DB::raw('NULL as tr_rev'),
            DB::raw('NULL as tr_net'),
            DB::raw('NULL as tr_roi'),
            DB::raw('NULL as tr_epc'),
            DB::raw('NULL as epc'),
            DB::raw('NULL as tr_cpa'),
            DB::raw('NULL as clicks'),
            DB::raw('NULL as ts_clicks'),
            DB::raw('NULL as trk_clicks'),
            DB::raw('NULL as lp_clicks'),
            DB::raw('NULL as lp_ctr'),
            DB::raw('NULL as ctr'),
            DB::raw('NULL as tr_cvr'),
            DB::raw('NULL as ecpm'),
            DB::raw('NULL as lp_cr'),
            DB::raw('NULL as lp_cpc')
        ]);
        $widgets_query->where('campaign_id', $campaign->id);
        $widgets_query->whereBetween('end_time', [$data['start'], $data['end']]);
        $widgets_query->where(DB::raw('JSON_EXTRACT(data, "$.summary.conversionMetrics[*].name")'), 'LIKE', '%' . $data['search'] . '%');

        return $widgets_query;
    }

    public function getContentQuery($campaign, $data)
    {
        $contents_query = Ad::select([
            '*',
            DB::raw('null as payout'),
            DB::raw('null as clicks'),
            DB::raw('null as lp_views'),
            DB::raw('null as lp_clicks'),
            DB::raw('null as total_conversions'),
            DB::raw('null as total_actions'),
            DB::raw('null as total_actions_cr'),
            DB::raw('null as cr'),
            DB::raw('null as total_revenue'),
            DB::raw('null as cost'),
            DB::raw('null as profit'),
            DB::raw('null as roi'),
            DB::raw('null as cpc'),
            DB::raw('null as cpa'),
            DB::raw('null as epc'),
            DB::raw('null as lp_ctr'),
            DB::raw('null as lp_views_cr'),
            DB::raw('null as lp_clicks_cr'),
            DB::raw('null as lp_cpc')
        ]);
        $contents_query->where('ads.campaign_id', $campaign->campaign_id);
        $contents_query->where('name', 'LIKE', '%' . $data['search'] . '%');

        return $contents_query;
    }

    public function getAdGroupQuery($campaign, $data)
    {
        //
    }

    public function getDomainQuery($campaign, $data)
    {
        //
    }

    public function getPerformanceQuery($campaign, $data)
    {
        //
    }

    public function getPerformanceData($campaign, $time_range)
    {
        return $campaign->twitterReports()->whereBetween('end_time', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
    }

    public function getDomainData($campaign, $time_range)
    {
        return [];
    }

    public function addSiteBlock($campaign, $data)
    {
        //
    }

    public function targets(Campaign $campaign)
    {
        //
    }

    public function blockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function unblockWidgets(Campaign $campaign, $widgets)
    {
        //
    }

    public function changeBugget(Campaign $campaign, $data)
    {
        $api = new TwitterAPI(UserProvider::where([
            'provider_id' => $campaign->provider_id,
            'open_id' => $campaign->open_id
        ])->first(), $campaign->advertiser_id);

        $budget = 0;

        if (!isset($data->budgetSetType) || $data->budgetSetType == 1) {
            $budget = $data->budget;
        } else {
            $campaign_data = $api->getCampaign($campaign->campaign_id)->toArray();

            $campaign_budget = $campaign_data['total_budget_amount_local_micro'] / 1E6;

            if ($data->budgetSetType == 2) {
                $budget = $campaign_budget + ($data->budgetUnit == 1 ? $data->budget : $campaign_budget * $data->budget / 100);

                if (!empty($data->budgetMax) && $budget > $data->budgetMax) {
                    $budget = $data->budgetMax;
                }
            } else {
                $budget = $campaign_budget - ($data->budgetUnit == 1 ? $data->budget : $campaign_budget * $data->budget / 100);

                if (!empty($data->budgetMin) && $budget < $data->budgetMin) {
                    $budget = $data->budgetMin;
                }
            }
        }

        $api->updateCampaignBudget($campaign, $budget);
    }

    public function changeCampaignBid(Campaign $campaign, $data)
    {
        $api = new TwitterAPI(UserProvider::where([
            'provider_id' => $campaign->provider_id,
            'open_id' => $campaign->open_id
        ])->first(), $campaign->advertiser_id);

        foreach ($data->adGroups as $item) {
            $api->updateLineItemBid($item->id, $item->data->bid);
        }
    }

    public function storeCampaignVendors($vendor) {
        $api = new TwitterAPI(UserProvider::where([
            'provider_id' => 3,
            'open_id' => $vendor['selectedAccount']
        ])->first(), $vendor['selectedAdvertiser']);

        try {
            $promotable_users = $this->api()->getPromotableUsers();

            $campaign_data = $api->saveCampaign();
            $line_item_data = $api->saveLineItem($campaign_data);

            foreach (request('cards') as $card) {
                foreach ($card['media'] as $media_item) {
                    $media = $this->api()->uploadMedia($promotable_users, $media_item['media']);
                    $media_library = $this->api()->createMediaLibrary($media->media_key);

                    if ($card['type'] == 'IMAGE') {
                        $card_data = $api->createWebsiteCard($media->media_key, $card);
                    } else {
                        $card_data = $api->createVideoWebsiteCard($media->media_key, $card);
                    }

                    foreach ($card['tweetTexts'] as $tweet_text) {
                        $tweet_data = $api->createTweet($card_data, $promotable_users, $card, $tweet_text);
                        $promoted_tweet = $api->createPromotedTweet($line_item_data->getId(), $tweet_data);
                    }
                }
            }

            Helper::pullCampaign();
        } catch (Exception $e) {
            $this->rollback($campaign_data ?? null, $line_item_data ?? null, $card_data ?? null);

            if ($e instanceof TwitterAdsException && is_array($e->getErrors())) {
                event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                    'errors' => [$e->getErrors()[0]->message],
                    'vendor' => 'twitter',
                    'vendorName' => 'Twitter'
                ]));

                return [
                    'errors' => [$e->getErrors()[0]->message]
                ];
            } else {
                event(new \App\Events\CampaignVendorCreated(auth()->id(), [
                    'errors' => [$e->getMessage()],
                    'vendor' => 'twitter',
                    'vendorName' => 'Twitter'
                ]));

                return [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        event(new \App\Events\CampaignVendorCreated(auth()->id(), [
            'success' => 1,
            'vendor' => 'twitter',
            'vendorName' => 'Twitter'
        ]));

        return [];
    }
}
