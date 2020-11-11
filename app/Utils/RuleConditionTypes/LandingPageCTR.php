<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RedtrackReport;

class LandingPageCTR extends Root
{
    public function check($redtrack_data, $rule_condition)
    {
        $sum_clicks = RedtrackReport::sum($redtrack_data, 'lp_clicks');
        $sum_views = RedtrackReport::sum($redtrack_data, 'lp_views');

        return parent::compare($sum_clicks / $sum_views, $rule_condition->amount, $rule_condition->operation);
    }
}
