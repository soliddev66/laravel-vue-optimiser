<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceCPA extends Root
{
    public function check($campaign, $performance_data, $rule_condition)
    {
        $sum_spends = ReportData::sum($campaign, $performance_data, 'spend');
        $sum_conversions = ReportData::sum($campaign, $performance_data, 'conversions');

        return parent::compare($sum_spends / $sum_conversions, $rule_condition->amount, $rule_condition->operation);
    }
}
