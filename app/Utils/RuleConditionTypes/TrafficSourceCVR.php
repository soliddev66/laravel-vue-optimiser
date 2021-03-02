<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceCVR extends Root
{
    public function check($campaign, $performance_data, $rule_condition)
    {
        $sum_conversions = ReportData::sum($campaign, $performance_data, 'conversions');
        $sum_clicks = ReportData::sum($campaign, $performance_data, 'click');

        return parent::compare($sum_conversions / $sum_clicks * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
