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
            $report_data = $campaign->redtrackReport()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();

            if (count($report_data) && $this->checkConditions($rule, $campaign, $report_data)) {
                echo 'PASSED', "\n";
            }
        }

        return 0;
    }

    private function checkConditions($rule, $campaign, $report_data)
    {
        foreach ($rule->ruleConditionGroups as $rule_condition_group) {
            $is_adapt = true;

            foreach ($rule_condition_group->ruleConditions as $rule_condition) {
                $rule_condition_type_class = 'App\\Utils\\RuleConditionTypes\\' . $rule_condition->ruleConditionType->provider;

                if (class_exists($rule_condition_type_class) && (new $rule_condition_type_class)->check($report_data, $rule_condition)) {
                    continue;
                }

                $is_adapt = false;
            }

            if ($is_adapt) {
                return true;
            }
        }

        return false;
    }
}
