<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerRevenue extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition, $calculation_type)
    {
        $sum_revenues = ReportDatam($redtrack_data, 'revenue', $calculation_type);

        return parent::compare($sum_revenues, $rule_condition->amount, $rule_condition->operation);
    }
}
