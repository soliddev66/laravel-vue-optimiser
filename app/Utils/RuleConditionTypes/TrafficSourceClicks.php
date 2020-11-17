<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceClicks extends Root
{
    public function check($performance_data, $rule_condition)
    {
        $sum_clicks = ReportData::sum($performance_data, 'clicks');

        return parent::compare($sum_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}