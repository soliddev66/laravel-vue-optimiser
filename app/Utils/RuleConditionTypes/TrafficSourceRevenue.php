<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrafficSourceRevenue extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $revenues = ReportData::sum($campaign, $redtrack_data, 'revenue', $calculation_type);

        return parent::compare($revenues, $rule_condition->amount, $rule_condition->operation);
    }
}
