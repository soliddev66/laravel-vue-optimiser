<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceSpent extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $spends = ReportData::sum($campaign, $performance_data, 'spend', $calculation_type);

        return parent::compare($spends, $rule_condition->amount, $rule_condition->operation);
    }
}
