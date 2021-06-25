<?php

namespace App\Utils\RuleActions;

use Exception;

class PauseCampaign extends Root
{
    public function process($campaign, &$log, $rule_data = null)
    {
        $log['effect'] = [
            'campaign' => $campaign
        ];

        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $ad_vendor_class)->status($campaign);
            $log['effect']['paused'] = true;
            echo 'Campaign was being paused', "\n";
        } catch (Exception $e) {
            echo "Campaign wasn't being paused\n";
            $log['effect']['paused'] = false;
            $log['effect']['message'] = $e->getMessage();
        }
    }

    public function visual($campaign, &$log, $rule_data = null)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign,
            'paused' => true
        ];
    }
}
