<?php

namespace App\Endpoints;

use DateTime;
use Exception;

use Carbon\Carbon;

use App\Helpers\GeminiClient;

use App\Vendors\Twitter\Creative\WebsiteCard;
use App\Vendors\Twitter\Creative\MediaLibrary;

use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\Tweet;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\LineItem;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\Campaign;
use Hborras\TwitterAdsSDK\TwitterAds\Creative\PromotedTweet;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\FundingInstrument;

use Hborras\TwitterAdsSDK\TwitterAds\Fields\TweetFields;


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

    public function createCampaign()
    {
        try {
            $campaign = new Campaign();
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

            if (!empty(request('campaignDurationInDays'))) {
                $campaign->setDurationInDays(request('campaignDurationInDays'));
            }

            if (!empty(request('campaignFrequencyCap'))) {
                $campaign->setFrequencyCap(request('campaignFrequencyCap'));
            }

            return $campaign->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createLineItem($campaign)
    {
        try {
            $line_item = new LineItem();
            $line_item->setCampaignId($campaign->getId());
            $line_item->setName(request('adGroupName'));
            $line_item->setProductType(request('adGroupProductType'));
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

            if (!request('adGroupAutomaticallySelectBid') && !empty(request('adGroupBidType'))) {
                $line_item->setBidType(request('adGroupBidType'));
            }

            if (!empty(request('adGroupTotalBudgetAmountLocalMicro'))) {
                $line_item->setTotalBudgetAmountLocalMicro(request('adGroupTotalBudgetAmountLocalMicro'));
            }

            if (!empty(request('adGroupPrimaryWebEventTag'))) {
                $line_item->setPrimaryWebEventTag(request('adGroupPrimaryWebEventTag'));
            }

            if (!empty(request('adGroupOptimization'))) {
                $line_item->setOptimization(request('adGroupOptimization'));
            }

            if (!empty(request('adGroupBidUnit'))) {
                $line_item->setBidUnit(request('adGroupBidUnit'));
            }

            if (!empty(request('adGroupChargeBy'))) {
                $line_item->setChargeBy(request('adGroupChargeBy'));
            }

            if (!empty(request('adGrouptrackingTags'))) {
                $line_item->setTrackingTags(request('adGrouptrackingTags'));
            }

            if (!empty(request('adGroupAdvertiserUserId'))) {
                $line_item->setAdvertiserUserId(request('adGroupAdvertiserUserId'));
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

    public function createTweet($card)
    {
        try {
            $param = [
                'card_uri' => $card->getCardUri(),
                'as_user_id' => $this->open_id,
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

            return Tweet::create($this->account, request('text') . rand(), $param);
        } catch (Exception $e) {
            throw $e;
        }
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

    public function uploadMedia()
    {
        $file = storage_path('app/public/images/') . request('cardMedia');
        $mime = mime_content_type($file);
        return $this->client->upload(['media' => $file, 'media_type' => $mime, 'additional_owners' => $this->open_id], true);
    }

    public function createMediaLibrary($media_key)
    {
        $media_library = new MediaLibrary();
        $media_library->setMediaKey($media_key);
        return $media_library->save();
    }
}