<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class ChangeCampaignBid extends Root
{
    public function process($campaign, &$log, $rule_data = null)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'bid' => $rule_data
        ];

        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->changeCampaignBid($campaign, $rule_data->bid);
            echo "Campaign's bid was being changed\n";
            $log['effect']['changed'] = true;
        } catch (Exception $e) {
            echo "Error happened. Campaign's bid wasn't being changed\n";
            $log['effect']['changed'] = false;
            $log['effect']['message'] = $e->getMessage();
        }
    }

    public function visual($campaign, &$log, $rule_data = null)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'bid' => $rule_data
        ];
    }
}
