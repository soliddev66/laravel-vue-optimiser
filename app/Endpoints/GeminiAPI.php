<?php

namespace App\Endpoints;

use App\Helpers\GeminiClient;
use Carbon\Carbon;

class GeminiAPI
{
    private $client;

    public function __construct($user_info)
    {
        $this->client = new GeminiClient($user_info);
    }

    public function getAdvertisers()
    {
        return $this->client->call('GET', 'advertiser');
    }

    public function getLanguages()
    {
        return $this->client->call('GET', 'dictionary/language');
    }

    public function getCountries()
    {
        return $this->client->call('GET', 'dictionary/woeid/?type=country');
    }

    public function createAdvertiser($name)
    {
        return $this->client->call('POST', 'advertisersignup', [
            'advertiserName' => $name
        ]);
    }

    public function getCampaign($campaign_id)
    {
        return $this->client->call('GET', 'campaign/' . $campaign_id);
    }

    public function getCampaigns()
    {
        return $this->client->call('GET', 'campaign');
    }

    public function getAdGroup($ad_group_id)
    {
        return $this->client->call('GET', 'adgroup/' . $ad_group_id);
    }

    public function getAd($ad_id)
    {
        return $this->client->call('GET', 'ad/' . $ad_id);
    }

    public function getAds($ad_group_ids, $advertiser_id)
    {
        return $this->client->call('GET', 'ad?adGroupId=' . implode('&adGroupId=', $ad_group_ids) . '&advertiserid=' . $advertiser_id);
    }

    public function createAd($campaign_data, $ad_group_data)
    {
        return $this->client->call('POST', 'ad', [
            'adGroupId' => $ad_group_data['id'],
            'advertiserId' => request('selectedAdvertiser'),
            'campaignId' => $campaign_data['id'],
            'description' => request('description'),
            'displayUrl' => request('displayUrl'),
            'landingUrl' => request('targetUrl'),
            'sponsoredBy' => request('brandname'),
            'imageUrlHQ' => $this->encodeUrl(request('imageUrlHQ')),
            'imageUrl' => $this->encodeUrl(request('imageUrl')),
            'title' => request('title'),
            'status' => 'ACTIVE'
        ]);
    }

    public function updateAdStatus($ad_group_id, $ad_id, $status)
    {
        return $this->client->call('PUT', 'ad', [
            'adGroupId' => $ad_group_id,
            'id' => $ad_id,
            'status' => $status
        ]);
    }

    public function deleteAds($ad_ids)
    {
        return $this->client->call('DELETE', 'ad?id=' . implode('&id=', $ad_ids));
    }

    public function getCampaignAttribute($campaign_id)
    {
        return $this->client->call('GET', 'targetingattribute?pt=CAMPAIGN&pi=' . $campaign_id);
    }

    public function getAdGroups($campaign_id, $advertiser_id)
    {
        return $this->client->call('GET', 'adgroup?campaignId=' . $campaign_id . '&advertiserid=' . $advertiser_id);
    }

    public function getAdsByCampaign($campaign_id, $advertiser_id)
    {
        return $this->client->call('GET', 'ad?campaignId=' . $campaign_id . '&advertiserid=' . $advertiser_id);
    }

    public function updateAds($body)
    {
        return $this->client->call('PUT', 'ad', $body);
    }

    public function createCampaign()
    {
        return $this->client->call('POST', 'campaign', [
            'advertiserId' => request('selectedAdvertiser'),
            'budget' => request('campaignBudget'),
            'budgetType' => request('campaignBudgetType'),
            'campaignName' => request('campaignName'),
            'channel' => request('campaignType'),
            'language' => request('campaignLanguage'),
            'biddingStrategy' => request('campaignStrategy'),
            'conversionRuleConfig' => ['conversionCounting' => request('campaignConversionCounting')],
            'status' => 'ACTIVE'
        ]);
    }

    public function updateCampaign($campaign)
    {
        return $this->client->call('PUT', 'campaign', [
            'id' => $campaign->campaign_id,
            'advertiserId' => request('selectedAdvertiser'),
            'budget' => request('campaignBudget'),
            'budgetType' => request('campaignBudgetType'),
            'campaignName' => request('campaignName'),
            'channel' => request('campaignType'),
            'language' => request('campaignLanguage'),
            'biddingStrategy' => request('campaignStrategy'),
            'conversionRuleConfig' => ['conversionCounting' => request('campaignConversionCounting')],
            'status' => 'ACTIVE'
        ]);
    }

    public function deleteCampaign($campaign_id)
    {
        return $this->client->call('DELETE', 'campaign/' . $campaign_id);
    }

    public function updateCampaignStatus($campaign)
    {
        return $this->client->call('PUT', 'campaign', [
            'id' => $campaign->campaign_id,
            'status' => $campaign->status
        ]);
    }

    private function getBids()
    {
        if (request('campaignType') === 'SEARCH_AND_NATIVE') {
            return [[
                'priceType' => 'CPC',
                'value' => request('bidAmount'),
                'channel' => 'SEARCH'
            ], [
                'priceType' => 'CPC',
                'value' => request('bidAmount'),
                'channel' => 'NATIVE'
            ]];
        } else {
            return [[
                'priceType' => 'CPC',
                'value' => request('bidAmount'),
                'channel' => request('campaignType')
            ]];
        }
    }

    public function createAdGroup($campaign_data)
    {
        $data = [
            'adGroupName' => request('adGroupName'),
            'advertiserId' => request('selectedAdvertiser'),
            'bidSet' => [
                'bids' => $this->getBids()
            ],
            'campaignId' => $campaign_data['id'],
            'startDateStr' => request('scheduleType') === 'IMMEDIATELY' ? Carbon::now()->format('Y-m-d') : request('campaignStartDate'),
            'endDateStr' => request('scheduleType') === 'IMMEDIATELY' ? '' : request('campaignEndDate'),
            'status' => 'ACTIVE'
        ];
        if (in_array(request('campaignStrategy'), ['OPT_ENHANCED_CPC', 'OPT_POST_INSTALL', 'OPT_CONVERSION'])) {
            $data['biddingStrategy'] = request('campaignStrategy');
        }

        return $this->client->call('POST', 'adgroup', $data);
    }

    public function updateAdGroup($campaign_data)
    {
        $data = [
            'id' => request('adGroupID'),
            'adGroupName' => request('adGroupName'),
            'advertiserId' => request('selectedAdvertiser'),
            'bidSet' => [
                'bids' => $this->getBids()
            ],
            'campaignId' => $campaign_data['id'],
            'startDateStr' => request('scheduleType') === 'IMMEDIATELY' ? Carbon::now()->format('Y-m-d') : request('campaignStartDate'),
            'endDateStr' => request('scheduleType') === 'IMMEDIATELY' ? '' : request('campaignEndDate'),
            'status' => 'ACTIVE'
        ];
        if (in_array(request('campaignStrategy'), ['OPT_ENHANCED_CPC', 'OPT_POST_INSTALL', 'OPT_CONVERSION'])) {
            $data['biddingStrategy'] = request('campaignStrategy');
        }

        return $this->client->call('PUT', 'adgroup', $data);
    }

    public function updateAdGroups($body)
    {
        return $this->client->call('PUT', 'adgroup', $body);
    }

    public function updateAdGroupStatus($ad_group_id, $status)
    {
        return $this->client->call('PUT', 'adgroup', [
            'id' => $ad_group_id,
            'status' => $status
        ]);
    }

    public function deleteAdGroups($ad_group_ids)
    {
        return $this->client->call('DELETE', 'adgroup?id=' . implode('&id=', $ad_group_ids));
    }

    public function updateAd($campaign_data, $ad_group_data)
    {
        return $this->client->call('PUT', 'ad', [
            'id' => request('adID'),
            'adGroupId' => $ad_group_data['id'],
            'advertiserId' => request('selectedAdvertiser'),
            'campaignId' => $campaign_data['id'],
            'description' => request('description'),
            'displayUrl' => request('displayUrl'),
            'landingUrl' => request('targetUrl'),
            'sponsoredBy' => request('brandname'),
            'imageUrlHQ' => $this->encodeUrl(request('imageUrlHQ')),
            'imageUrl' => $this->encodeUrl(request('imageUrl')),
            'title' => request('title'),
            'status' => 'ACTIVE'
        ]);
    }

    public function deleteAttributes()
    {
        if (!count(request('dataAttributes'))) {
            return;
        }

        return $this->client->call('DELETE', 'targetingattribute?id=' . implode('&id=', request('dataAttributes')));
    }

    public function createAttributes($campaign_data)
    {
        $request_body = [];
        $body = [
            'advertiserId' => request('selectedAdvertiser'),
            'parentType' => 'CAMPAIGN',
            'parentId' => $campaign_data['id'],
            'status' => 'ACTIVE',
            'include' => 'TRUE'
        ];

        if (count(request('campaignLocation'))) {
            foreach (request('campaignLocation') as $key => $location) {
                $request_body[] = $body + ['type' => 'WOEID', 'value' => $location];
            }
        }

        if (count(request('attributes'))) {
            foreach (request('attributes') as $key => $attribute) {
                if ($attribute['gender']) {
                    $request_body[] = $body + ['type' => 'GENDER', 'value' => $attribute['gender']];
                }

                if ($attribute['age']) {
                    foreach ($attribute['age'] as $index => $age) {
                        $request_body[] = $body + ['type' => 'AGE', 'value' => $age];
                    }
                }

                if ($attribute['device']) {
                    $request_body[] = $body + ['type' => 'DEVICE', 'value' => $attribute['device']];
                }
            }
        }

        return $this->client->call('POST', 'targetingattribute', $request_body);
    }

    private function encodeUrl($url)
    {
        return preg_replace_callback('#://([^/]+)/([^?]+)#', function ($match) {
            return '://' . $match[1] . '/' . join('/', array_map('rawurlencode', explode('/', $match[2])));
        }, $url);
    }
}
