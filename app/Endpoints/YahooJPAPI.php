<?php

namespace App\Endpoints;

use App\Helpers\YahooJPClient;
use App\Vngodev\Helper;
use Carbon\Carbon;

class YahooJPAPI
{
    private $client;

    public function __construct($user_info)
    {
        $this->client = new YahooJPClient($user_info);
    }

    public function getAccounts()
    {
        return $this->client->call('POST', 'AccountService/get', [
            'startIndex' => 1
        ]);
    }

    public function getCampaignsByAccountId($id)
    {
        return $this->client->call('POST', 'CampaignService/get', [
            'accountId' => $id
        ]);
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
        return $this->client->call('POST', 'AdGroupAdService/get', [
            'accountId' => $advertiser_id,
            'adGroupIds' => $ad_group_ids
        ]);
    }

    public function createAd($ads)
    {
        return $this->client->call('POST', 'AdGroupAdService/add', $ads);
    }

    public function createMedia($media)
    {
        return $this->client->call('POST', 'MediaService/add', $media);
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
        return $this->client->call('POST', 'AdGroupService/get', [
            'accountId' => $advertiser_id,
            'campaignIds' => [$campaign_id]
        ]);
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

    public function updateAd($ads)
    {
        return $this->client->call('PUT', 'ad', $ads);
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
            'status' => 'ACTIVE'
        ];

        if (count(request('campaignLocation'))) {
            foreach (request('campaignLocation') as $key => $item) {
                $request_body[] = $body + ['type' => 'WOEID', 'value' => $item];
            }
        }

        if (count(request('campaignGender'))) {
            foreach (request('campaignGender') as $key => $item) {
                $request_body[] = $body + ['type' => 'GENDER', 'value' => $item];
            }
        }

        if (count(request('campaignAge'))) {
            foreach (request('campaignAge') as $key => $item) {
                $request_body[] = $body + ['type' => 'AGE', 'value' => $item];
            }
        }

        if (count(request('campaignDevice'))) {
            foreach (request('campaignDevice') as $key => $item) {
                $request_body[] = $body + ['type' => 'DEVICE', 'value' => $item];
            }
        }

        if (!empty(request('campaignSupplyGroup1A'))) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_1_A', 'bidModifier' => request('bidAmount') + (request('bidAmount') * (request('campaignSupplyGroup1A') / 100))];
        }

        if (!empty(request('campaignSupplyGroup1B'))) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_1_B', 'bidModifier' => request('bidAmount') + (request('bidAmount') * (request('campaignSupplyGroup1B') / 100))];
        }

        if (!empty(request('campaignSupplyGroup2A'))) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_2_A', 'bidModifier' => request('bidAmount') + (request('bidAmount') * (request('campaignSupplyGroup2A') / 100))];
        }

        if (!empty(request('campaignSupplyGroup2B'))) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_2_B', 'bidModifier' => request('bidAmount') + (request('bidAmount') * (request('campaignSupplyGroup2B') / 100))];
        }

        if (!empty(request('campaignSupplyGroup3A'))) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_3_A', 'bidModifier' => request('bidAmount') + (request('bidAmount') * (request('campaignSupplyGroup3A') / 100))];
        }

        if (!empty(request('campaignSupplyGroup3B'))) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_3_B', 'bidModifier' => request('bidAmount') + (request('bidAmount') * (request('campaignSupplyGroup3B') / 100))];
        }

        if (!empty(request('campaignSiteBlock'))) {
            $campaign_site_blocks = explode(',', request('campaignSiteBlock'));

            if (count($campaign_site_blocks) > 0) {
                foreach ($campaign_site_blocks as $item) {
                    $request_body[] = $body + ['type' => 'SITE_BLOCK', 'exclude' => 'TRUE', 'value' => trim($item)];
                }
            }
        }

        if (count(request('supportedSiteCollections'))) {
            foreach (request('supportedSiteCollections') as $item) {
                $request_body[] = $body + ['type' => 'SITE_X_DEVICE', 'exclude' => 'FALSE', 'value' => $item['key'], 'bidModifier' => request('bidAmount') + request('bidAmount') * $item['bidModifier'] / 100];
            }
        }

        return $this->client->call('POST', 'targetingattribute', $request_body);
    }
}
