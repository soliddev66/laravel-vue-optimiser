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

    public function deleteMedia($media)
    {
        return $this->client->call('POST', 'MediaService/remove', $media);
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
        return $this->client->call('POST', 'CampaignService/add', [
            'accountId' => request('selectedAdvertiser'),
            'operand' => [[
                'type' => 'STANDARD',
                'accountId' => request('selectedAdvertiser'),
                'biddingStrategy' => [
                    'biddingStrategyType' => request('campaignBidStrategy')
                ],
                'budget' => [
                    'amount' => request('campaignBudget'),
                    'budgetDeliveryMethod' => request('campaignBudgetDeliveryMethod')
                ],
                'campaignBiddingStrategy' => $this->getCampaignBiddingStrategy(),
                'campaignGoal' => request('campaignGoal'),
                'campaignName' => request('campaignName'),
                'startDate' => request('campaignStartDate'),
                'endDate' => request('campaignEndDate'),
                'userStatus' => request('campaignStatus')
            ]]
        ]);
    }

    public function updateCampaign($campaign)
    {

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

    private function getCampaignBiddingStrategy()
    {
        $campaignBiddingStrategy = [
            'campaignBiddingStrategyType' => request('campaignCampaignBidStrategy')
        ];

        switch(request('campaignCampaignBidStrategy')) {
            case 'MAX_CPC':
                $campaignBiddingStrategy['maxCpcBidValue'] = request('campaignMaxCpcBidValue');
                break;

            case 'MAX_VCPM':
                $campaignBiddingStrategy['maxVcpmBidValue'] = request('campaignMaxVcpmBidValue');
                break;

            case 'MAX_CPV':
                $campaignBiddingStrategy['maxCpvBidValue'] = request('campaignMaxCpvBidValue');
                break;

            case 'MAX_VCPM':
                $campaignBiddingStrategy['targetCpaBidValue'] = request('campaignTargetCpaBidValue');
                break;
        }

        return $campaignBiddingStrategy;
    }

    private function getBid()
    {
        if (request('campaignBidStrategy') == 'MANUAL_CPC') {
            return [
                'manualCPCBid' => [
                    'maxCpc' => request('adGroupBidAmount')
                ]
            ];
        }

        if (request('campaignBidStrategy') == 'MANUAL_CPV') {
            return [
                'manualCPVBid' => [
                    'maxCpv' => request('adGroupBidAmount')
                ]
            ];
        }

        return [];
    }

    public function updateCampaignStatus($campaign)
    {
        return $this->client->call('PUT', 'campaign', [
            'id' => $campaign->campaign_id,
            'status' => $campaign->status
        ]);
    }


    public function createAdGroup($campaign_id)
    {
        $data = [
            'accountId' => request('selectedAdvertiser'),
            'operand' => [[
                'accountId' => request('selectedAdvertiser'),
                'campaignId' => $campaign_id,
                'adGroupName' => request('adGroupName'),
                'adGroupBiddingStrategy' => $this->getCampaignBiddingStrategy(),
                'bid' => $this->getBid(),
                'device' => count(request('campaignDevices')) ? request('campaignDevices') : ['NONE'],
                'deviceApp' => count(request('campaignDeviceApps')) ? request('campaignDeviceApps') : ['NONE'],
                'deviceOs' => count(request('campaignDeviceOs')) ? request('campaignDeviceOs') : ['NONE'],
                'userStatus' => request('campaignStatus')
            ]]
        ];

        return $this->client->call('POST', 'AdGroupService/add', $data);
    }

    public function createTargets($campaign_id, $ad_group_id)
    {
        $data = [
            'accountId' => request('selectedAdvertiser'),
            'operand' => []
        ];

        $account = [
            'accountId' => request('selectedAdvertiser'),
            'campaignId' => $campaign_id,
            'adGroupId' => $ad_group_id
        ];


        foreach (request('campaignAges') as $item) {
            $data['operand'][] = $account + [
                'target' => [
                    'targetType' => 'AGE_TARGET',
                    'ageTarget' => [
                        'age' => $item,
                        'estimateFlg' => 'ACTIVE'
                    ]
                ]
            ];
        }

        foreach (request('campaignGenders') as $item) {
            $data['operand'][] = $account + [
                'target' => [
                    'targetType' => 'GENDER_TARGET',
                    'genderTarget' => [
                        'gender' => $item,
                        'estimateFlg' => 'ACTIVE'
                    ]
                ]
            ];
        }

        foreach (request('campaignDevices') as $item) {
            $data['operand'][] = $account + [
                'target' => [
                    'targetType' => 'DEVICE_TARGET',
                    'deviceTarget' => [
                        'deviceType' => $item,
                    ]
                ]
            ];
        }

        $this->client->call('POST', 'AdGroupTargetService/add', $data);
    }

    public function getTargets($advertiser_id, $ad_group_ids)
    {
        return $this->client->call('POST', 'AdGroupTargetService/get', [
            'accountId' => $advertiser_id,
            'targetTypes' => [
                'AGE_TARGET',
                'GENDER_TARGET',
                'DEVICE_TARGET',
                'APP_TARGET',
                'OS_TARGET'
            ]
        ]);
    }

    public function updateAdGroup($campaign_data)
    {

    }

    public function updateAdGroups($body)
    {

    }

    public function updateAdGroupStatus($ad_group_id, $status)
    {

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

    public function updateAd($ads)
    {

    }

    public function deleteAttributes()
    {

    }

    public function createAttributes($campaign_data)
    {

    }
}
