<?php

namespace App\Utils\RuleActions;

class BlockWidgetsPushlisher extends Root
{
    public function process($campaign, &$log, $rule_data = null)
    {
        $log['effect'] = [
            'campaign' => $campaign->name,
            'widgets' => $rule_data
        ];

        try {
            $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
            (new $ad_vendor_class)->blockWidgets($campaign, $rule_data->widgets);
            $log['effect']['blocked'] = true;
            echo "Campaign's widgets were being blocked\n";
        } catch (Exception $e) {
            $log['effect']['blocked'] = false;
            $log['effect']['message'] = $e->getMessage();
            echo "Campaign's widgets weren't being blocked\n";
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
