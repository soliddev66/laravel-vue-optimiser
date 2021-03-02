<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceClicks extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $sum_clicks = ReportData::sum($campaign, $performance_data, 'clicks', $calculation_type);

        return parent::compare($sum_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
