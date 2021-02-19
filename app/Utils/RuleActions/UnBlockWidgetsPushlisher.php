<?php

namespace App\Utils\RuleActions;

class UnBlockWidgetsPushlisher extends Root
{
    public function process($campaign, &$log, $rule_data = null)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'widgets' => $rule_data
        ];

        try {
            $adVendorClass = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $adVendorClass)->unblockWidgets($campaign, $rule_data);
            $log['effect']['blocked'] = true;
            echo "Campaign's widgets were being unblocked\n";
        } catch (Exception $e) {
            echo "Campaign's widgets weren't being unblocked\n";
        }
    }

    public function visual($campaign, &$log, $rule_data = null)
    {
        $log['visual-effect'] = [
            'campaign' => $campaign->name,
            'widgets' => $rule_data
        ];
    }
}
