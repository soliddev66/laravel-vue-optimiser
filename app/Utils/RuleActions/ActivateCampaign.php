<?php

namespace App\Utils\RuleActions;

use Exception;

class ActivateCampaign extends Root
{
    public function process($campaign, &$log, $rule_data = null)
    {
        $log['effect'] = [
            'campaign' => $campaign
        ];

        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $ad_vendor_class)->status($campaign);
            $log['effect']['activated'] = true;
            echo 'Campaign was being activated', "\n";
        } catch (Exception $e) {
            $log['effect']['activated'] = false;
            $log['effect']['message'] = $e->getMessage();
            echo "Error happened. Campaign wasn't being activated\n";
        }
    }

    public function visual($campaign, &$log, $rule_data = null)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign,
            'activated' => true
        ];
    }
}
