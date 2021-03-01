<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceSpent extends Root
{
    public function check($campaign, $performance_data, $rule_condition)
    {
        $sum_spends = ReportData::sum($campaign, $performance_data, 'spend');

        return parent::compare($sum_spends, $rule_condition->amount, $rule_condition->operation);
    }
}
