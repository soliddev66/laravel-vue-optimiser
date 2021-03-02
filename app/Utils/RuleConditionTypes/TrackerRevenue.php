<?php

namespace App\Utils\RuleConditionTypes;

use App\Utils\ReportData;

class TrackerRevenue extends Root
{
    public function check($campaign, $redtrack_data, $rule_condition)
    {
        $sum_revenues = ReportDatam($redtrack_data, 'revenue');

        return parent::compare($sum_revenues, $rule_condition->amount, $rule_condition->operation);
    }
}
