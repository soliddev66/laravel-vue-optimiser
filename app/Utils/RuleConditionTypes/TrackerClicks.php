<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class TrackerClicks extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_clicks = RedtrackReport::sum($redtrack_data, 'clicks');

        return parent::compare($sum_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
