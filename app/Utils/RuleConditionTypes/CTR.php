<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class CTR extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_clicks = RedtrackReport::sum($redtrack_data, 'clicks');
        $sum_lp_clicks = RedtrackReport::sum($redtrack_data, 'lp_clicks');

        return parent::compare($sum_clicks / $sum_lp_clicks, $rule_condition->amount, $rule_condition->operation);
    }
}
