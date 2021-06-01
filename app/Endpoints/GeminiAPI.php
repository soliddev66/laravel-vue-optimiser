<?php

namespace App\Endpoints;

use App\Helpers\GeminiClient;
use Carbon\Carbon;

use App\Vngodev\Helper;

class GeminiAPI
{
    private $client;
    private $user_info;

    public function __construct($user_info)
    {
        $this->user_info = $user_info;
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
        return $this->client->call('GET', 'campaign?advertiserId=' . implode('&advertiserId=', $this->user_info->advertisers));
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

    public function createAd($ads)
    {
        return $this->client->call('POST', 'ad', $ads);
    }

    public function getBbsxdSupportedSites()
    {
        return $this->client->call('GET', 'dictionary/bbsxd_supported_sites');
    }

    public function getBbsxdSupportedSiteGroups()
    {
        return $this->client->call('GET', 'dictionary/bbsxd_supported_site_groups');
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

    public function updateAds($body)
    {
        return $this->client->call('PUT', 'ad', $body);
    }

    public function createCampaign($body)
    {
        return $this->client->call('POST', 'campaign', $body);
    }

    public function updateCampaign($campaign)
    {
        return $this->client->call('PUT', 'campaign', [
            'id' => $campaign->campaign_id,
            'advertiserId' => request('selectedAdvertiser'),
            'budget' => request('campaignBudget'),
            'budgetType' => request('campaignBudgetType'),
            'campaignName' => request('campaignName'),
            'objective' => request('campaignObjective'),
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

    public function updateCampaignBudget($campaign_id, $budget)
    {
        return $this->client->call('PUT', 'campaign', [
            'id' => $campaign_id,
            'budget' => $budget
        ]);
    }

    public function createAdGroup($body)
    {
        return $this->client->call('POST', 'adgroup', $body);
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

    public function createAttributes($campaign_data, $data)
    {
        $request_body = [];
        $body = [
            'advertiserId' => $data['selectedAdvertiser'],
            'parentType' => 'CAMPAIGN',
            'parentId' => $campaign_data['id'],
            'status' => 'ACTIVE'
        ];

        if (count($data['campaignLocation'])) {
            foreach ($data['campaignLocation'] as $key => $item) {
                $request_body[] = $body + ['type' => 'WOEID', 'value' => $item];
            }
        }

        if (count($data['campaignGender'])) {
            foreach ($data['campaignGender'] as $key => $item) {
                $request_body[] = $body + ['type' => 'GENDER', 'value' => $item];
            }
        }

        if (count($data['campaignAge'])) {
            foreach ($data['campaignAge'] as $key => $item) {
                $request_body[] = $body + ['type' => 'AGE', 'value' => $item];
            }
        }

        if (count($data['campaignDevice'])) {
            foreach ($data['campaignDevice'] as $key => $item) {
                $request_body[] = $body + ['type' => 'DEVICE', 'value' => $item];
            }
        }

        if (!empty($data['campaignSupplyGroup1A'])) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_1_A', 'bidModifier' => $data['bidAmount'] + ($data['bidAmount'] * ($data['campaignSupplyGroup1A'] / 100))];
        }

        if (!empty($data['campaignSupplyGroup1B'])) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_1_B', 'bidModifier' => $data['bidAmount'] + ($data['bidAmount'] * ($data['incrementType1b'] * $data['campaignSupplyGroup1B'] / 100))];
        }

        if (!empty($data['campaignSupplyGroup2A'])) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_2_A', 'bidModifier' => $data['bidAmount'] + ($data['bidAmount'] * ($data['incrementType2a'] * $data['campaignSupplyGroup2A'] / 100))];
        }

        if (!empty($data['campaignSupplyGroup2B'])) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_2_B', 'bidModifier' => $data['bidAmount'] + ($data['bidAmount'] * ($data['incrementType2b'] * $data['campaignSupplyGroup2B'] / 100))];
        }

        if (!empty($data['campaignSupplyGroup3A'])) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_3_A', 'bidModifier' => $data['bidAmount'] + ($data['bidAmount'] * ($data['incrementType3a'] * $data['campaignSupplyGroup3A'] / 100))];
        }

        if (!empty($data['campaignSupplyGroup3B'])) {
            $request_body[] = $body + ['type' => 'SUPPLY_GROUP', 'value' => 'GROUP_3_B', 'bidModifier' => $data['bidAmount'] + ($data['bidAmount'] * ($data['incrementType3b'] * $data['campaignSupplyGroup3B'] / 100))];
        }

        if (!empty($data['campaignSiteBlock'])) {
            $campaign_site_blocks = explode(PHP_EOL, $data['campaignSiteBlock']);

            if (count($campaign_site_blocks) > 0) {
                foreach ($campaign_site_blocks as $item) {
                    $request_body[] = $body + ['type' => 'SITE_BLOCK', 'exclude' => 'TRUE', 'value' => trim($item)];
                }
            }
        }

        if (count($data['supportedSiteCollections'])) {
            foreach ($data['supportedSiteCollections'] as $item) {
                if ($item['type'] == 'site') {
                    $request_body[] = $body + ['type' => 'SITE_X_DEVICE', 'exclude' => 'FALSE', 'value' => $item['key'], 'bidModifier' => $data['bidAmount'] + $item['incrementType'] * $data['bidAmount'] * $item['bidModifier'] / 100];
                } elseif ($item['type'] == 'group') {
                    $request_body[] = $body + ['type' => 'SITE_GROUP_X_DEVICE', 'exclude' => 'FALSE', 'value' => $item['key'], 'bidModifier' => $data['bidAmount'] + $item['incrementType'] * $data['bidAmount'] * $item['bidModifier'] / 100];
                }
            }
        }

        return $this->client->call('POST', 'targetingattribute', $request_body);
    }

    public function addAttributes($body)
    {
        return $this->client->call('POST', 'targetingattribute', $body);
    }

    public function updateAttributes($body) {
        return $this->client->call('PUT', 'targetingattribute', $body);
    }
}
