<?php

namespace App\Endpoints;

use App\Helpers\GeminiClient;
use App\Vendors\Twitter\Creative\MediaLibrary;
use App\Vendors\Twitter\Creative\WebsiteCard;
use App\Vendors\Twitter\Creative\Tweets;
use Carbon\Carbon;
use DateTime;
use Exception;
use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\Campaign;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\FundingInstrument;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\LineItem;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\PromotableUser;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\Tweet;
use Hborras\TwitterAdsSDK\TwitterAds\Creative\PromotedTweet;
use Hborras\TwitterAdsSDK\TwitterAds\Fields\TweetFields;
use Hborras\TwitterAdsSDK\TwitterAds\Fields\AnalyticsFields;


class TwitterAPI
{
    private $client;

    private $account;

    private $open_id;

    public function __construct($user_info, $account_id = null)
    {
        $this->open_id = $user_info->open_id;
        $this->client = TwitterAds::init(env('TWITTER_CLIENT_ID'), env('TWITTER_CLIENT_SECRET'), $user_info->token, $user_info->secret_token, $account_id, env('TWITTER_SANDBOX'));
        if ($account_id) {
            $this->account = (new Account($account_id))->read();
        }
    }

    public function getAdvertisers()
    {
        return $this->client->getAccounts()->getCollection();
    }

    public function getCampaign($campaign_id)
    {
        return $this->account->getCampaigns($campaign_id);
    }

    public function getCampaigns()
    {
        return $this->account->getCampaigns()->getCollection();
    }

    public function getAdGroups($campaign_id)
    {
        return $this->account->getLineItems('', [
            'campaign_ids' => $campaign_id
        ])->getCollection();
    }

    public function getCountries()
    {
        return $this->client->get('targeting_criteria/locations', ['location_type' => 'COUNTRIES'])->getBody()->data;
    }

    public function getFundingInstruments()
    {
        return $this->account->getFundingInstruments()->getCollection();
    }

    public function getAdGroupCategories()
    {
        return $this->client->get('iab_categories')->getBody()->data;
    }

    public function getPromotedTweets($line_item_ids)
    {
        return (new PromotedTweet)->all([
            'line_item_ids' => implode(',', $line_item_ids)
        ])->getCollection();
    }

    public function deletePromotedTweet($promoted_tweet_id)
    {
        (new PromotedTweet($promoted_tweet_id))->delete();
    }

    public function getTweetPreviews($tweet_id)
    {
        try {
            return Tweet::preview($this->account, [
                TweetFields::ID => $tweet_id
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function saveCampaign($campaign_id = null)
    {
        try {
            $campaign = new Campaign($campaign_id);
            $campaign->setFundingInstrumentId(request('fundingInstrument'));
            $campaign->setDailyBudgetAmountLocalMicro(request('campaignDailyBudgetAmountLocalMicro') * 1E6);
            $campaign->setName(request('campaignName'));
            $campaign->setEntityStatus(request('campaignStatus'));
            $campaign->setStartTime(request('campaignStartTime'));

            if (!empty(request('campaignEndTime'))) {
                $campaign->setEndTime(request('campaignEndTime'));
            }

            if (!empty(request('campaignTotalBudgetAmountLocalMicro'))) {
                $campaign->setTotalBudgetAmountLocalMicro(request('campaignTotalBudgetAmountLocalMicro'));
            }

            return $campaign->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteCampaign($campaign_id)
    {
        try {
            (new Campaign($campaign_id))->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateCampaignStatus($campaign_instance)
    {
        $campaign = new Campaign($campaign_instance->campaign_id);
        $campaign->setEntityStatus($campaign_instance->status);
        $campaign->save();
    }

    public function updateAdGroupStatus($ad_group_id, $status)
    {
        $line_item = new LineItem($ad_group_id);
        $line_item->setEntityStatus($status);
        $line_item->save();
    }

    public function deleteLineItem($line_item_id)
    {
        try {
            (new LineItem($line_item_id))->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function deleteCard($card_id)
    {
        try {
            (new WebsiteCard($card_id))->delete();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function saveLineItem($campaign, $line_item_id = null)
    {
        try {
            $line_item = new LineItem($line_item_id);
            $line_item->setCampaignId($campaign->getId());
            $line_item->setName(request('adGroupName'));
            $line_item->setProductType('PROMOTED_TWEETS');
            $line_item->setPlacements(request('adGroupPlacements'));
            $line_item->setObjective(request('adGroupObjective'));
            $line_item->setEntityStatus(request('adGroupStatus'));
            $line_item->setCategories(request('adGroupCategories'));
            $line_item->setAdvertiserDomain(request('adGroupAdvertiserDomain'));

            if (!empty(request('adGroupBidAmountLocalMicro'))) {
                $line_item->setBidAmountLocalMicro(request('adGroupBidAmountLocalMicro') * 1E6);
            }

            if (!empty(request('adGroupStartTime'))) {
                $line_item->setStartTime(request('adGroupStartTime'));
            }

            if (!empty(request('adGroupEndTime'))) {
                $line_item->setEndTime(request('adGroupEndTime'));
            }

            if (request('adGroupAutomaticallySelectBid')) {
                $line_item->setAutomaticallySelectBid(true);
            }

            if (request('adGroupAutomaticallySelectBid') && !empty(request('adGroupBidType'))) {
                $line_item->setBidType(request('adGroupBidType'));
            }

            if (!empty(request('adGroupTotalBudgetAmountLocalMicro'))) {
                $line_item->setTotalBudgetAmountLocalMicro(request('adGroupTotalBudgetAmountLocalMicro'));
            }

            $line_item->setOptimization('DEFAULT');

            if (!empty(request('adGroupBidUnit'))) {
                $line_item->setBidUnit(request('adGroupBidUnit'));
            }

            if (!empty(request('adGroupChargeBy'))) {
                $line_item->setChargeBy(request('adGroupChargeBy'));
            }

            return $line_item->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createWebsiteCard($card_media_key)
    {
        try {
            $website_card = new WebsiteCard();
            $website_card->setName(request('cardName'));
            $website_card->setMediaKey($card_media_key);
            $website_card->setWebsiteTitle(request('cardWebsiteTitle'));
            $website_card->setWebsiteUrl(request('cardWebsiteUrl'));

            return $website_card->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createTweet($card, $promotable_users)
    {
        try {
            $param = [
                'card_uri' => $card->getCardUri(),
                'as_user_id' => $promotable_users->getCollection()[0]->getUserId(),
            ];

            if (!empty(request('tweetNullcast'))) {
                $param['nullcast'] = request('tweetNullcast');
            }

            if (!empty(request('tweetTrimUser'))) {
                $param['trim_user'] = request('tweetTrimUser');
            }

            if (!empty(request('tweetVideoCTA'))) {
                $param['tweet_mode'] = request('tweetVideoCTA');
            }

            if (!empty(request('tweetVideoCTAValue'))) {
                $param['video_cta_value'] = request('tweetVideoCTAValue');
            }

            if (!empty(request('tweetVideoTitle'))) {
                $param['video_title'] = request('tweetVideoTitle');
            }

            if (!empty(request('tweetVideoDescription'))) {
                $param['video_description'] = request('tweetVideoDescription');
            }

            return Tweet::create($this->account, request('tweetText'), $param);
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getTweet($tweet_id)
    {
        return (new Tweets())->all([
            'tweet_ids' => $tweet_id,
            'tweet_type' => 'PUBLISHED'
        ])->getCollection();
    }

    public function createPromotedTweet($line_item, $tweet)
    {
        try {
            $promoted_tweet = new PromotedTweet();
            $promoted_tweet->setLineItemId($line_item->getId());
            $promoted_tweet->setTweetId($tweet->id);

            return $promoted_tweet->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getPromotableUsers()
    {
        $promotable_user = new PromotableUser();
        return $promotable_user->all();
    }

    public function uploadMedia($promotable_users)
    {
        $file = storage_path('app/public/images/') . request('cardMedia');
        $mime = mime_content_type($file);
        $promotable_user_ids = [];
        foreach ($promotable_users->getCollection() as $key => $promotable_user) {
            array_push($promotable_user_ids, $promotable_user->getUserId());
        }
        return $this->client->upload(['media' => $file, 'media_type' => $mime, 'additional_owners' => $promotable_user_ids], true);
    }

    public function createMediaLibrary($media_key)
    {
        $media_library = new MediaLibrary();
        $media_library->setMediaKey($media_key);
        return $media_library->save();
    }

    public function getCampaignStats($param)
    {
        $campaign = new Campaign();
        $resource = str_replace(Campaign::RESOURCE_REPLACE, $campaign->getTwitterAds()->getAccountId(), Campaign::RESOURCE_STATS);

        return $campaign->getTwitterAds()->get($resource, $param)->getBody()->data;
    }
}