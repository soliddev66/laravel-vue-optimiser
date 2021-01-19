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
                'campaignBiddingStrategy' => $campaignBiddingStrategy,
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

    public function createAdGroup($campaign_id)
    {
        $campaignBiddingStrategy = [
            'campaignBiddingStrategyType' => request('campaignCampaignBidStrategy')
        ];

        switch(request('campaignBiddingStrategyType')) {
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

        $bid = [
            'type' => request('campaignBidStrategy')
        ];

        switch (request('campaignBidStrategy')) {
            case 'MANUAL_CPC':
                $bid['manualCPCBid'] = [
                    'maxCpc' => request('adGroupBidAmount')
                ];
                break;
            case 'MANUAL_CPV':
                $bid['manualCPVBid'] = [
                    'maxCpv' => request('adGroupBidAmount')
                ];
                break;
        }

        $data = [
            'accountId' => request('selectedAdvertiser'),
            'operand' => [
                'accountId' => request('selectedAdvertiser'),
                'campaignId' => $campaign_id,
                'adGroupName' => request('adGroupName'),
                'adGroupBiddingStrategy' => $campaignBiddingStrategy,
                'bid' => $bid,
                'device' => request('campaignDevices'),
                'userStatus' => 'ACTIVE'
            ]
        ];

        return $this->client->call('POST', 'AdGroupService/add', $data);
    }

    public function createTargets($campaign_id, $ad_group_id)
    {
        $data = [
            'accountId' => request('selectedAdvertiser'),
            'operand' => [
                'accountId' => request('selectedAdvertiser'),
                'campaignId' => $campaign_id,
                'adGroupId' => $ad_group_id
            ]
        ];

        foreach (request('campaignAges') as $item) {
            $this->client->call('POST', 'AdGroupTargetService/add', $data + [
                'operand' => [
                    'targetType' => 'AGE_TARGET',
                    'target' => [
                        'ageTarget' => [
                            'age' => $item,
                            'estimateFlg' => 'ACTIVE'
                        ]
                    ]
                ]
            ]);
        }

        foreach (request('campaignGenders') as $item) {
            $this->client->call('POST', 'AdGroupTargetService/add', $data + [
                'operand' => [
                    'targetType' => 'GENDER_TARGET',
                    'target' => [
                        'genderTarget' => [
                            'gender' => $item,
                            'estimateFlg' => 'ACTIVE'
                        ]
                    ]
                ]
            ]);
        }

        foreach (request('campaignDevices') as $item) {
            $this->client->call('POST', 'AdGroupTargetService/add', $data + [
                'operand' => [
                    'targetType' => 'DEVICE_TARGET',
                    'target' => [
                        'deviceTarget' => [
                            'deviceType' => $item,
                        ]
                    ]
                ]
            ]);
        }
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

    public function deleteAdGroups($ad_group_ids)
    {

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
