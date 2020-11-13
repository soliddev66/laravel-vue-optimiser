<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerConversions extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_conversions = ReportData::sum($redtrack_data, 'conversions');

        return parent::compare($sum_conversions, $rule_condition->amount, $rule_condition->operation);
    }
}
