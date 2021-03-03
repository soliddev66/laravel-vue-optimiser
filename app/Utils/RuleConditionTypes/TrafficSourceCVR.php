<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceCVR extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $conversions = ReportData::sum($campaign, $performance_data, 'conversions', $calculation_type);
        $clicks = ReportData::sum($campaign, $performance_data, 'click', $calculation_type);

        return parent::compare($clicks ? $conversions / $clicks * 100 : INF, $rule_condition->amount, $rule_condition->operation);
    }
}
