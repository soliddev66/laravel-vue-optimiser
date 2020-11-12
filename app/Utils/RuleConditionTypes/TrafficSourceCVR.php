<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceCVR extends Root
{
    public function check($performance_data, $rule_condition)
    {
        $sum_conversions = ReportData::sum($performance_data, 'conversions');
        $sum_clicks = ReportData::sum($performance_data, 'click');

        return parent::compare($sum_conversions / $sum_clicks * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
