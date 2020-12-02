<?php

namespace App\Utils\AdVendors;

use Exception;

use App\Models\Campaign;
use App\Models\Provider;
use App\Endpoints\TwitterAPI;

use App\Jobs\PullCampaign;

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
        $user_provider = auth()->user()->providers()->where('provider_id', $campaign['provider_id'])->where('open_id', $campaign['open_id'])->first();
        if ($user_provider) {
            $api = new TwitterAPI($user_provider, $campaign->advertiser_id);

            $instance = $api->getCampaign($campaign->campaign_id)->toArray();

            $instance['provider'] = $campaign->provider->slug;
            $instance['open_id'] = $campaign['open_id'];
            $instance['advertiser_id'] = $campaign->advertiser_id;
            $instance['instance_id'] = $campaign['id'];

            $instance['adGroups'] = [];

            $ad_groups = $api->getAdGroups($campaign->campaign_id);

            if ($ad_groups && count($ad_groups) > 0) {
                foreach ($ad_groups as $ad_group) {
                    $instance['adGroups'][] = [
                        'id' => $ad_group->getId(),
                        'name' => $ad_group->getName(),
                        'bid_amount_local_micro' => $ad_group->getBidAmountLocalMicro(),
                        'bid_type' => $ad_group->getBidType(),
                        'automatically_select_bid' => $ad_group->getAutomaticallySelectdBid(),
                        'product_type' => $ad_group->getProductType(),
                        'placements' => $ad_group->getPlacements(),
                        'objective' => $ad_group->getObjective(),
                        'entity_status' => $ad_group->getEntityStatus(),
                        'include_sentiment' => $ad_group->getIncludeSentiment(),
                        'total_budget_amount_local_micro' => $ad_group->getTotalBudgetAmountLocalMicro(),
                        'start_time' => $ad_group->getStartTime(),
                        'end_time' => $ad_group->getEndTime(),
                        'primary_web_event_tag' => $ad_group->getPrimaryWebEventTag(),
                        'optimization' => $ad_group->getOptimization(),
                        'bid_unit' => $ad_group->getBidUnit(),
                        'charge_by' => $ad_group->getChargeBy(),
                        'advertiser_domain' => $ad_group->getAdvertiserDomain(),
                        'tracking_tags' => $ad_group->getTrackingTags(),
                        'advertiser_user_id' => $ad_group->getAdvertiserUserId(),
                        'categories' => $ad_group->getCategories(),
                    ];
                }

                $promoted_tweets = $api->getPromotedTweet($ad_groups[0]->getId());

                $tweets = $api->getTweet($promoted_tweets[0]->getTweetId());

                $instance['ads'] = [];

                if ($tweets && count($tweets) > 0) {
                    foreach ($tweets as $tweet) {
                        $instance['ads'][] = [
                            'id' => $tweet->getId(),
                            'full_text' => $tweet->getFullText(),
                            'nullcast' => $tweet->getNullCast(),
                            'trim_user' => $tweet->getTrimUser(),
                            'tweet_mode' => $tweet->getTweetMode(),
                            'video_cta' => $tweet->getVideoCTA(),
                            'video_cta_value' => $tweet->getVideoCTAValue(),
                            'video_title' => $tweet->getVideoTitle(),
                            'video_description' => $tweet->getVideoDescription()
                        ];
                    }
                }
            }

            return $instance;
        }

        return [];
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
        $data = [];
        $api = $this->api();

        try {
            try {
                $promotable_users = $this->api()->getPromotableUsers();
                $media = $this->api()->uploadMedia($promotable_users);
                $media_library = $this->api()->createMediaLibrary($media->media_key);
            } catch (Exception $e) {
                throw $e;
            }

            try {
                $campaign_data = $api->createCampaign();
            } catch (Exception $e) {
                throw $e;
            }

            try {
                $line_item_data = $api->createLineItem($campaign_data);
            } catch (Exception $e) {
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                throw $e;
            }

            try {
                $card_data = $api->createWebsiteCard($media->media_key);
            } catch (Exception $e) {
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                // $line_item_data->delete();
                throw $e;
            }

            try {
                $tweet_data = $api->createTweet($card_data, $promotable_users);
            } catch (Exception $e) {
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                // $line_item_data->delete();
                // $card_data->delete();
                throw $e;
            }

            try {
                $promoted_tweet = $api->createPromotedTweet($line_item_data, $tweet_data);
            } catch (Exception $e) {
                // TO-DO: Dispatch job
                // $campaign_data->delete();
                // $line_item_data->delete();
                // $card_data->delete();
                // $tweet_data->delete();
                throw $e;
            }

            // try {
            //     $data = [
            //         'previewData' => $api->getTweetPreviews($tweet_data->id)
            //     ];
            // } catch (Exception $e) {
            //     throw $e;
            // }

            PullCampaign::dispatch(auth()->user());
        } catch (Exception $e) {
            if ($e instanceof TwitterAdsException && is_array($e->getErrors())) {
                $data = [
                    'errors' => [$e->getErrors()[0]->message]
                ];
            } else {
                $data = [
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        return $data;
    }

    public function update(Campaign $campaign)
    {

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

    public function pullRedTrack($campaign)
    {

    }
}
