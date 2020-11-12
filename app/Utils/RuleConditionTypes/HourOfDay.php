<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class HourOfDay extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_hour_of_days = ReportData::sum($redtrack_data, 'hour_of_day');

        return parent::compare($sum_hour_of_days, $rule_condition->amount, $rule_condition->operation);
    }
}
