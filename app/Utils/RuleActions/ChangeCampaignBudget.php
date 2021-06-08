<?php

namespace App\Utils\RuleActions;

use Exception;

use App\Models\Campaign;
use App\Endpoints\GeminiAPI;

class ChangeCampaignBudget extends Root
{
    public function process($campaign, &$log, $rule_data = null)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'bugget' => $rule_data
        ];

        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $ad_vendor_class)->changeBugget($campaign, $rule_data);
            echo "Campaign's budget was being changed\n";
            $log['effect']['changed'] = true;
        } catch (Exception $e) {
            echo "Error happened. Campaign's budget wasn't being changed\n";
            $log['effect']['changed'] = false;
            $log['effect']['message'] = $e->getMessage();
        }
    }

    public function visual($campaign, &$log, $rule_data = null)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'bugget' => $rule_data
        ];
    }
}
