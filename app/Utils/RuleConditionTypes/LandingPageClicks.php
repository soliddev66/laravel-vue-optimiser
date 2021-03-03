<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class LandingPageClicks extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $clicks = ReportData::sum($campaign, $redtrack_data, 'lp_clicks', $calculation_type);

        return parent::compare($clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
