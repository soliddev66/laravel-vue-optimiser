<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceClicks extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $clicks = ReportData::sum($campaign, $performance_data, 'clicks', $calculation_type);

        return parent::compare($clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
