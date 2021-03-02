<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class LandingPageClicks extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition)
    {
        $sum_lp_clicks = ReportData::sum($campaign, $redtrack_data, 'lp_clicks');

        return parent::compare($sum_lp_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
