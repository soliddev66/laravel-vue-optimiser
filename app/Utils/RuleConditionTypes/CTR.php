<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class CTR extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition)
    {
        $sum_clicks = ReportData::sum($campaign, $redtrack_data, 'lp_clicks');
        $sum_views = ReportData::sum($campaign, $redtrack_data, 'lp_views');
        if ($sum_views) {
            $ctr = $sum_clicks / $sum_views * 100;
        } else {
            $ctr = INF;
        }

        return parent::compare($ctr, $rule_condition->amount, $rule_condition->operation);
    }
}
