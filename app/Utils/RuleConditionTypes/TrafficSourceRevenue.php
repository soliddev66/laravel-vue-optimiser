<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceRevenue extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_revenues = ReportData::sum($redtrack_data, 'revenue');

        return parent::compare($sum_revenues, $rule_condition->amount, $rule_condition->operation);
    }
}