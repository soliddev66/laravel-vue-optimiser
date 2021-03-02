<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceCVR extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $sum_conversions = ReportData::sum($campaign, $performance_data, 'conversions', $calculation_type);
        $sum_clicks = ReportData::sum($campaign, $performance_data, 'click', $calculation_type);

        return parent::compare($sum_conversions / $sum_clicks * 100, $rule_condition->amount, $rule_condition->operation);
    }
}
