<?php

namespace App\Utils\RuleActions;

use Exception;

class ChangeCampaignBid extends Root
{
    public function process($campaign, &$log, $rule_data = null)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'bid' => $rule_data
        ];

        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $ad_vendor_class)->changeCampaignBid($campaign, $rule_data);
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
