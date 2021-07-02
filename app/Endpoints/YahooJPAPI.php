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

    public function getAdvertisers()
    {
        return $this->client->call('POST', 'AccountService/get', [
            'startIndex' => 1
        ]);
    }

    public function getCampaignGoals($account_id)
    {
        return $this->client->call('POST', 'AccountAuthorityService/get', [
            'accountIds' => [$account_id],
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

    public function getCampaign($advertiser_id, $campaign_id)
    {
        return $this->client->call('POST', 'CampaignService/get', [
            'accountId' => $advertiser_id,
            'campaignIds' => [
                $campaign_id
            ]
        ]);
    }

    public function getAdGroups($advertiser_id, $ad_group_ids)
    {
        return $this->client->call('POST', 'AdGroupService/get', [
            'accountId' => $advertiser_id,
            'adGroupIds' => $ad_group_ids
        ]);
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

    public function deleteMedia($media)
    {
        return $this->client->call('POST', 'MediaService/remove', $media);
    }

    public function uploadVideo($body, $file, $file_name)
    {
        return $this->client->upload('VideoService/upload', $body, $file, $file_name);
    }

    public function updateAdStatus($data)
    {
        return $this->client->call('POST', 'AdGroupAdService/set', $data);
    }

    public function deleteAds($ad_ids)
    {
        return $this->client->call('DELETE', 'ad?id=' . implode('&id=', $ad_ids));
    }

    public function getCampaignAttribute($campaign_id)
    {
        return $this->client->call('GET', 'targetingattribute?pt=CAMPAIGN&pi=' . $campaign_id);
    }

    public function getCampaignAdGroups($campaign_id, $advertiser_id)
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

    public function createCampaign($body)
    {
        return $this->client->call('POST', 'CampaignService/add', $body);
    }

    public function updateCampaign($body)
    {
        return $this->client->call('POST', 'CampaignService/set', $body);
    }

    public function updateCampaignData($body)
    {
        return $this->client->call('POST', 'CampaignService/set', $body);
    }

    public function deleteCampaign($advertiser_id, $campaign_id)
    {
        return $this->client->call('POST', 'CampaignService/remove', [
            'accountId' => $advertiser_id,
            'operand' => [[
                'accountId' => $advertiser_id,
                'campaignId' => $campaign_id
            ]]
        ]);
    }

    public function updateCampaignStatus($campaign)
    {
        return $this->client->call('POST', 'CampaignService/set', [
            'accountId' => $campaign->advertiser_id,
            'operand' => [[
                'accountId' => $campaign->advertiser_id,
                'campaignId' => $campaign->campaign_id,
                'userStatus' => $campaign->status
            ]]
        ]);
    }


    public function createAdGroup($body)
    {
        return $this->client->call('POST', 'AdGroupService/add', $body);
    }

    public function createTargets($campaign_id, $ad_group_id, $advertiser_id, $data, $is_replace = false)
    {
        $data = [
            'accountId' => $advertiser_id,
            'operand' => []
        ];

        $account = [
            'accountId' => $advertiser_id,
            'campaignId' => $campaign_id,
            'adGroupId' => $ad_group_id
        ];

        foreach ($data['campaignAges'] as $item) {
            $data['operand'][] = $account + [
                'target' => [
                    'targetType' => 'AGE_TARGET',
                    'ageTarget' => [
                        'age' => $item,
                        'estimateFlg' => 'PAUSED'
                    ]
                ]
            ];
        }

        foreach ($data['campaignGenders'] as $item) {
            $data['operand'][] = $account + [
                'target' => [
                    'targetType' => 'GENDER_TARGET',
                    'genderTarget' => [
                        'gender' => $item,
                        'estimateFlg' => 'PAUSED'
                    ]
                ]
            ];
        }

        foreach ($data['campaignDevices'] as $item) {
            $data['operand'][] = $account + [
                'target' => [
                    'targetType' => 'DEVICE_TARGET',
                    'deviceTarget' => [
                        'deviceType' => $item,
                    ]
                ]
            ];
        }

        return $this->client->call('POST', 'AdGroupTargetService/' . ($is_replace ? 'replace' : 'add'), $data);
    }

    public function getTargets($advertiser_id, $ad_group_ids, $campaign_id)
    {
        return $this->client->call('POST', 'AdGroupTargetService/get', [
            'accountId' => $advertiser_id,
            'campaignIds' => [$campaign_id],
            'targetTypes' => [
                'AGE_TARGET',
                'GENDER_TARGET',
                'DEVICE_TARGET',
                'APP_TARGET',
                'OS_TARGET'
            ]
        ]);
    }

    public function updateAdGroup($body)
    {
        return $this->client->call('POST', 'AdGroupService/set', $body);
    }

    public function updateAdGroups($body)
    {
        return $this->client->call('POST', 'AdGroupService/set', $body);
    }

    public function deleteAdGroup($campaign_id, $ad_group_id)
    {
        $this->client->call('POST', 'AdGroupTargetService/add', [
            'accountId' => request('selectedAdvertiser'),
            'operand' => [[
                'accountId' => request('selectedAdvertiser'),
                'campaignId' => $campaign_id,
                'adGroupId' => $ad_group_id
            ]]
        ]);
    }

    public function updateAd($data)
    {
        return $this->client->call('POST', 'AdGroupAdService/set', $data);
    }

    public function deleteAttributes()
    {

    }

    public function createAttributes($campaign_data)
    {

    }

    public function getReport($advertiser_id, $campaign_ids, $start_date, $end_date)
    {
        return $this->client->call('POST', 'StatsService/get', [
            'accountId' => $advertiser_id,
            'campaignIds' => $campaign_ids,
            'statsPeriod' => 'CUSTOM_DATE',
            'periodCustomDate' => [
                'statsStartDate' => $start_date,
                'statsEndDate' => $end_date
            ],
            'type' => 'CAMPAIGN'
        ]);
    }
}
