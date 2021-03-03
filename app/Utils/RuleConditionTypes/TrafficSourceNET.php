<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceNET extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $profits = ReportData::sum($campaign, $redtrack_data, 'profit', $calculation_type);

        return parent::compare($profits, $rule_condition->amount, $rule_condition->operation);
    }
}
