<?php

namespace App\Utils\AdVendors;

use Exception;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Campaign;
use App\Models\Provider;
use App\Models\UserTracker;
use App\Models\RedtrackReport;
use App\Endpoints\TwitterAPI;

use App\Jobs\PullCampaign;
use App\Jobs\DeleteCampaign;
use App\Jobs\DeleteAdGroup;
use App\Jobs\DeleteCard;

use Hborras\TwitterAdsSDK\TwitterAdsException;

class Twitter extends Root
{
    private function api()
    {
        $provider = Provider::where('slug', request('provider'))->first();
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

    public function cloneCampaignName(&$instance)
    {
        $instance['name'] = $instance['name'] . ' - Copy';
    }

    public function fundingInstruments()
    {
        $funding_instruments = $this->api()->getFundingInstruments();

        $result = [];

        foreach ($funding_instruments as $funding_instrument) {
            $result[] = [
                'id' => $funding_instrument->getId(),
                'name' => $funding_instrument->getName()
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
            $media = $this->api()->uploadMedia($promotable_users);
            $media_library = $this->api()->createMediaLibrary($media->media_key);

            $campaign_data = $api->saveCampaign();
            $line_item_data = $api->saveLineItem($campaign_data);
            $card_data = $api->createWebsiteCard($media->media_key);

            $tweet_data = $api->createTweet($card_data, $promotable_users);
            $promoted_tweet = $api->createPromotedTweet($line_item_data, $tweet_data);

            PullCampaign::dispatch(auth()->user());
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

    private function rollback($campaign_data = null,  $line_item_data = null, $card_data = null)
    {
        if ($campaign_data) {
            DeleteCampaign::dispatch(auth()->user(), $campaign_data->getId(), request('provider'), request('account'), request('advertiser'));
        }

        if ($line_item_data) {
            DeleteAdGroup::dispatch(auth()->user(), $line_item_data->getId(), request('provider'), request('account'), request('advertiser'));
        }

        if ($card_data) {
            DeleteCard::dispatch(auth()->user(), $card_data->getId(), request('provider'), request('account'), request('advertiser'));
        }
    }

    public function update(Campaign $campaign)
    {
        try {
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);

            $campaign_data = $api->saveCampaign($campaign->campaign_id);
            $line_item_data = $api->saveLineItem($campaign_data, request('adGroupID'));

            if (!request('saveCard')) {
                $api->deletePromotedTweet(request('promotedAdID'));

                $promotable_users = $api->getPromotableUsers();
                $media = $api->uploadMedia($promotable_users);
                $media_library = $api->createMediaLibrary($media->media_key);

                $card_data = $api->createWebsiteCard($media->media_key);
                $tweet_data = $api->createTweet($card_data, $promotable_users);
                $promoted_tweet = $api->createPromotedTweet($line_item_data, $tweet_data);
            }

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            return [
                'errors' => [$e->getMessage()]
            ];
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
            $api = new TwitterAPI(auth()->user()->providers()->where('provider_id', $campaign->provider_id)->where('open_id', $campaign->open_id)->first(), $campaign->advertiser_id);
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
            'summary_data' => new \stdClass
        ]);
    }

    public function adStatus(Campaign $campaign, $ad_group_id, $ad_id)
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
        $advertisers = (new TwitterAPI($user_provider))->getAdvertisers();

        $campaign_ids = [];

        foreach ($advertisers as $advertiser) {
            $campaigns = (new TwitterAPI($user_provider, $advertiser->getId()))->getCampaigns();

            if (is_array($campaigns)) {
                foreach ($campaigns as $item) {
                    $campaign = Campaign::firstOrNew([
                        'campaign_id' => $item->getId(),
                        'provider_id' => $user_provider->provider_id,
                        'user_id' => $user_provider->user_id,
                        'open_id' => $user_provider->open_id
                    ]);

                    $campaign->advertiser_id = $advertiser->getId();

                    $campaign->name = $item->getName();
                    $campaign->status = $item->getEntityStatus();
                    $campaign->budget = $item->getTotalBudgetAmountLocalMicro() ? ($item->getTotalBudgetAmountLocalMicro() / 1000000) : ($item->getDailyBudgetAmountLocalMicro() / 1000000);
                    $campaign->save();
                    $campaign_ids[] = $campaign->id;
                }
            }
        }

        Campaign::where([
            'user_id' => $user_provider->user_id,
            'provider_id' => $user_provider->provider_id,
            'open_id' => $user_provider->open_id
        ])->whereNotIn('id', $campaign_ids)->delete();
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

    public function pullRedTrack($campaign)
    {
        $tracker = UserTracker::where('provider_id', $campaign->provider_id)
            ->where('provider_open_id', $campaign->open_id)
            ->first();

        if ($tracker) {
            $client = new Client();
            $date = Carbon::now()->format('Y-m-d');
            $url = 'https://api.redtrack.io/report?api_key=' . $tracker->api_key . '&date_from=' . $date . '&date_to=' . $date . '&group=hour_of_day&sub3=[' . $campaign->campaign_id . ']&sub9=Twitter&tracks_view=true';
            $response = $client->get($url);

            $data = json_decode($response->getBody(), true);

            foreach ($data as $i => $value) {
                $value['date'] = $date;
                $value['user_id'] = $campaign->user_id;
                $value['campaign_id'] = $campaign->id;
                $value['provider_id'] = $campaign->provider_id;
                $value['open_id'] = $campaign->open_id;
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
