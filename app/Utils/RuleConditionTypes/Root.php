<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RuleCondition;

class Root
{
    public function compare($first_value, $second_value, $operation)
    {
        echo ' | ', $first_value, ' | ', $second_value, ' | ', RuleCondition::OPERATIONS[$operation];

        switch (RuleCondition::OPERATIONS[$operation]) {
            case 'LESS_THAN':
                return $first_value < $second_value;
            case 'GREATER_THAN':
                return $first_value > $second_value;
            case 'EQUAL':
                return $first_value == $second_value;
        }
    }
}
