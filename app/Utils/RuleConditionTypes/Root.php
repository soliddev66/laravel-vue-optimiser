<?php

namespace App\Utils\RuleConditionTypes;

use App\Models\RuleCondition;

class Root
{
    public $data_log;

    public function compare($first_value, $second_value, $operation)
    {
        $this->data_log['first_value'] = $first_value;
        $this->data_log['second_value'] = $second_value;
        $this->data_log['operation'] = RuleCondition::OPERATIONS[$operation];

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
