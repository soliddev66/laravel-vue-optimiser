<?php

namespace App\Models;

use App\Helper\GeminiAPI;

class Gemini
{
    private $gemini;

    public function __construct($user_info) {
        $this->gemini = new GeminiAPI($user_info);
    }

    public function getCampaign($campaign_id) {
        return $this->gemini->call('GET', env('BASE_URL') . '/v3/rest/campaign/' . $campaign_id);
    }

    public function getAdGroup($ad_group_id) {
        return $this->gemini->call('GET', env('BASE_URL') . '/v3/rest/adgroup/' . $ad_group_id);
    }

    public function createAd($campaign_data, $ad_group_data) {
        return $this->gemini->call('POST', env('BASE_URL') . '/v3/rest/ad', [
            'adGroupId' => $ad_group_data['response']['id'],
            'advertiserId' => request('selectedAdvertiser'),
            'campaignId' => $campaign_data['response']['id'],
            'description' => request('description'),
            'displayUrl' => request('displayUrl'),
            'landingUrl' => request('targetUrl'),
            'sponsoredBy' => request('brandname'),
            'imageUrlHQ' => request('imageUrlHQ'),
            'imageUrl' => request('imageUrl'),
            'title' => request('title'),
            'status' => 'ACTIVE'
        ]);
    }
}