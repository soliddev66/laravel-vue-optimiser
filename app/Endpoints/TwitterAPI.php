<?php

namespace App\Endpoints;

use DateTime;
use Exception;

use Carbon\Carbon;

use App\Helpers\GeminiClient;

use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\LineItem;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\Campaign;
use Hborras\TwitterAdsSDK\TwitterAds\Creative\WebsiteCard;
use Hborras\TwitterAdsSDK\TwitterAds\Campaign\FundingInstrument;

class TwitterAPI
{
    private $client;

    private $account_id;

    public function __construct($user_info, $account_id)
    {
        $this->account_id = $account_id;
        $this->client = TwitterAds::init(env('TWITTER_CLIENT_ID'), env('TWITTER_CLIENT_SECRET'), $user_info->token, $user_info->secret_token, $account_id, env('TWITTER_SANDBOX'));
    }

    public function getAdvertisers()
    {
        return $this->client->getAccounts()->getCollection();
    }

    public function getCountries()
    {
        return $this->client->get('targeting_criteria/locations', ['location_type' => 'COUNTRIES'])->getBody()->data;
    }

    public function createAccount()
    {
        return (new Account())->save();
    }

    public function getFundingInstruments()
    {
        $account = new Account($this->account_id);
        $account->read();

        return $account->getFundingInstruments()->getCollection();
    }

    public function getAdGroupCategories()
    {
        return $this->client->get('iab_categories')->getBody()->data;
    }

    public function createCampaign()
    {
        try {
            $account = new Account($this->account_id);
            $account->read();

            $campaign = new Campaign();
            $campaign->setFundingInstrumentId(request('fundingInstrument'));
            $campaign->setDailyBudgetAmountLocalMicro(request('campaignDailyBudgetAmountLocalMicro') * 1E6);
            $campaign->setName(request('campaignName'));
            $campaign->setEntityStatus(request('campaignStatus'));
            $campaign->setStartTime(request('campaignStartTime'));

            return $campaign->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createLineItem($campaign)
    {
        try {
            $account = new Account($this->account_id);
            $account->read();

            $line_item = new LineItem();
            $line_item->setCampaignId($campaign->getId());
            $line_item->setName(request('adGroupName'));
            $line_item->setProductType(request('adGroupProductType'));
            $line_item->setPlacements(request('adGroupPlacements'));
            $line_item->setObjective(request('adGroupObjective'));
            $line_item->setBidAmountLocalMicro(request('adGroupBidAmountLocalMicro') * 1E6);
            $line_item->setEntityStatus(request('adGroupStatus'));
            $line_item->setCategories(request('adGroupCategories'));
            $line_item->setAdvertiserDomain(request('adGroupAdvertiserDomain'));

            return $line_item->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function createCard()
    {
        try {
            $website_card = new WebsiteCard();
            $website_card->setName(request('cardName'));
            $website_card->setImageMediaId(request('cardMediaKey'));
            $website_card->setWebsiteTitle(request('cardWebsiteTitle'));
            $website_card->setWebsiteUrl(request('cardWebsiteUrl'));

            return $website_card->save();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function uploadMedia()
    {
        return $this->client->upload(['media' => base_path() . '/kitten.jpg', 'media_type' => 'image/jpg'], true);
    }

    public function createMediaLibrary($media_key)
    {
        return $this->client->post('accounts/' . $this->account_id . '/media_library?media_key=' . $media_key);
    }
}