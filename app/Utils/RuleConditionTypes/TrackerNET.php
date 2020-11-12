<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerNET extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_profits = ReportData::sum($redtrack_data, 'profit');

        return parent::compare($sum_profits, $rule_condition->amount, $rule_condition->operation);
    }
}
