<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Rule;

class RuleAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rule:action {ruleId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $rule_id = $this->argument('ruleId');

        $rule = Rule::find($rule_id);

        $time_range_class = 'App\\Utils\\TimeFrames\\' . $rule->timeRange->provider;

        $time_range = (new $time_range_class)->get();

        foreach ($rule->campaigns as $campaign) {
            $redtrack_data = $campaign->redtrackReport()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
            $performance_data = $campaign->performanceStats()->whereBetween('day', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();

            if ($this->checkConditions($rule, $campaign, $redtrack_data, $performance_data)) {
                echo 'PASSED', "\n";
                $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule->ruleAction->provider;

                if (class_exists($rule_action_class)) {
                    (new $rule_action_class)->process($campaign);
                }
            } else {
                echo 'NOPASSED', "\n";
            }

            echo "\n";
        }

        return 0;
    }

    private function checkConditions($rule, $campaign, $redtrack_data, $performance_data)
    {
        foreach ($rule->ruleConditionGroups as $rule_condition_group) {
            $is_adapt = true;

            echo 'GROUP ', $rule_condition_group->id, "\n";

            foreach ($rule_condition_group->ruleConditions as $rule_condition) {
                $rule_condition_type_class = 'App\\Utils\\RuleConditionTypes\\' . $rule_condition->ruleConditionType->provider;

                echo $rule_condition_type_class;

                if (class_exists($rule_condition_type_class)
                    && (
                        (
                            $rule_condition->ruleConditionType->report_source == 1
                            && count($redtrack_data)
                            && (new $rule_condition_type_class)->check($redtrack_data, $rule_condition)
                        )
                        ||
                        (
                            $rule_condition->ruleConditionType->report_source == 2
                            && count($performance_data)
                            && (new $rule_condition_type_class)->check($performance_data, $rule_condition)
                        )
                    )
                ) {
                    echo ' | OK', "\n";
                    continue;
                }

                $is_adapt = false;
                echo ' | NO', "\n";
                break;
            }

            if ($is_adapt) {
                return true;
            }
        }

        return false;
    }
}
