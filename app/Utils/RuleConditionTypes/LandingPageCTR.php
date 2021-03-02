<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class LandingPageCTR extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $sum_clicks = ReportData::sum($campaign, $redtrack_data, 'lp_clicks', $calculation_type);
        $sum_views = ReportData::sum($campaign, $redtrack_data, 'lp_views', $calculation_type);
        if ($sum_views) {
            $lp_ctr = $sum_clicks / $sum_views;
        } else {
            $lp_ctr = INF;
        }

        return parent::compare($lp_ctr, $rule_condition->amount, $rule_condition->operation);
    }
}
