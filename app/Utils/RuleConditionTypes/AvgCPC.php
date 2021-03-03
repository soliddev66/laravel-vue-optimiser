<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class AvgCPC extends Root
{
    public function check($campaign, $performance_data, $rule_condition, $calculation_type)
    {
        $spends = ReportData::sum($campaign, $performance_data, 'spend', $calculation_type);
        $clicks = ReportData::sum($campaign, $performance_data, 'click', $calculation_type);

        return parent::compare($clicks ? $spends / $clicks : INF, $rule_condition->amount, $rule_condition->operation);
    }
}
