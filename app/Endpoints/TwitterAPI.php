<?php

namespace App\Endpoints;

use Carbon\Carbon;

use App\Helpers\GeminiClient;

use Hborras\TwitterAdsSDK\TwitterAds;
use Hborras\TwitterAdsSDK\TwitterAds\Account;
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

    public function createFundingInstrument($param)
    {
        // $url = 'accounts/' . $this->account_id . '/funding_instruments?currency=' . $param['currency'] . '&start_time=' . $param['start_time'] . '&type=' . $param['type'];

        // if (!empty($param['end_time'])) {
        //     $url .= '&end_time=' . $param['end_time'];
        // }

        // if (!empty($param['credit_limit_local_micro'])) {
        //     $url .= '&credit_limit_local_micro=' . $param['credit_limit_local_micro'];
        // }

        // if (!empty($param['funded_amount_local_micro'])) {
        //     $url .= '&funded_amount_local_micro=' . $param['funded_amount_local_micro'];
        // }

        $url = 'accounts/' . $this->account_id . '/funding_instruments?currency=USD&start_time=2020-07-10T00:00:00Z&type=INSERTION_ORDER&end_time=2022-01-10T00:00:00Z&funded_amount_local_micro=140000000000';

        return $this->client->post($url);
    }
}