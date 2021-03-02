<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class EstimatedNET extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition)
    {
        $sum_profits = ReportData::sum($campaign, $redtrack_data, 'profit');

        return parent::compare($sum_profits, $rule_condition->amount, $rule_condition->operation);
    }
}
