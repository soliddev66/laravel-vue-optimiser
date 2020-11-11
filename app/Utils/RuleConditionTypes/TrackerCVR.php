<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class TrackerCVR extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_revenue = RedtrackReport::sum($redtrack_data, 'revenue');
        $sum_clicks = RedtrackReport::sum($redtrack_data, 'clicks');

        return parent::compare($sum_revenue / $sum_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
