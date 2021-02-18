<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;

use App\Models\User;
use App\Models\Rule;
use App\Models\RuleLog;
use App\Models\Campaign;

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

    private $log;

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

        $campaigns = Campaign::find(json_decode($rule->action_data)->ruleCampaigns);

        foreach ($campaigns as $campaign) {
            switch ($rule->ruleAction->calculation_type) {
                case 1:
                    $this->log = new RuleLog;

                    $this->log->rule_id = $rule_id;

                    $this->log->start_date = $time_range[0]->format('Y-m-d');
                    $this->log->end_date = $time_range[1]->format('Y-m-d');

                    $redtrack_data = $campaign->redtrackReport()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
                    $performance_data = $campaign->performanceStats()->whereBetween('day', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();

                    if ($this->checkConditions($rule, $redtrack_data, $performance_data)) {
                        echo 'PASSED', "\n";
                        $this->log->passed = true;

                        if ($rule->run_type == 1 || $rule->run_type == 3) {
                            $this->sendNotify();
                        }

                        if ($rule->run_type == 2 || $rule->run_type == 3) {
                            $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule->ruleAction->provider;

                            if (class_exists($rule_action_class)) {
                                (new $rule_action_class)->process($campaign);
                            }
                        }
                    } else {
                        echo 'NOPASSED', "\n";
                        $this->log->passed = false;
                    }

                    $this->log->data = json_encode($this->log->data_text);
                    $this->log->save();

                    break;

                case 2:
                    $redtrack_domain_data = $campaign->redtrackDomainStats()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();
                    foreach ($redtrack_domain_data as $data) {
                        $this->log = new RuleLog;

                        $this->log->rule_id = $rule_id;

                        $this->log->start_date = $time_range[0]->format('Y-m-d');
                        $this->log->end_date = $time_range[1]->format('Y-m-d');

                        if ($this->checkConditions($rule, [$data], [$data])) {
                            echo 'PASSED', "\n";
                            $this->log->passed = true;

                            if ($rule->run_type == 1 || $rule->run_type == 3) {
                                $this->sendNotify();
                            }

                            if ($rule->run_type == 2 || $rule->run_type == 3) {
                                $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule->ruleAction->provider;

                                if (class_exists($rule_action_class)) {
                                    (new $rule_action_class)->process($campaign, $data, $this->log->data_text);
                                }
                            }
                        } else {
                            echo 'NOPASSED', "\n";
                            $this->log->passed = false;
                            $this->log->data_text['effect'] = [
                                'campaign' => $campaign->name,
                                'site' => $data['sub1'],
                                'blocked' => false
                            ];
                        }

                        $this->log->data = json_encode($this->log->data_text);
                        $this->log->save();
                    }

                    break;

                case 3:
                    foreach ($campaign->ads as $ad) {
                        $redtrack_content_stats = $ad->redtrackContentStats()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();

                        $this->log = new RuleLog;

                        $this->log->rule_id = $rule_id;

                        $this->log->start_date = $time_range[0]->format('Y-m-d');
                        $this->log->end_date = $time_range[1]->format('Y-m-d');

                        if ($this->checkConditions($rule, $redtrack_content_stats, $redtrack_content_stats)) {
                            echo 'PASSED', "\n";
                            $this->log->passed = true;

                            if ($rule->run_type == 1 || $rule->run_type == 3) {
                                $this->sendNotify();
                            }

                            if ($rule->run_type == 2 || $rule->run_type == 3) {
                                $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule->ruleAction->provider;

                                if (class_exists($rule_action_class)) {
                                    (new $rule_action_class)->process($campaign, $ad);
                                }
                            }
                        } else {
                            echo 'NOPASSED', "\n";
                            $this->log->passed = false;
                        }

                        $this->log->data = json_encode($this->log->data_text);
                        $this->log->save();
                    }

                    break;

            }

            echo "\n";
        }

        return 0;
    }

    private function checkConditions($rule, $redtrack_data, $performance_data)
    {
        $data = [];
        $this->log->data_text = &$data;

        foreach ($rule->ruleConditionGroups as $rule_condition_group) {
            $is_adapt = true;

            $data_items = [];

            echo 'GROUP ', $rule_condition_group->id, "\n";
            $data_items['group'] = $rule_condition_group->id;
            $data_items['items'] = [];

            foreach ($rule_condition_group->ruleConditions as $rule_condition) {
                $data_item = [];
                $rule_condition_type_class = 'App\\Utils\\RuleConditionTypes\\' . $rule_condition->ruleConditionType->provider;

                $data_item['ruleConditionType'] = $rule_condition_type_class;

                $rule_condition_type_instance = new $rule_condition_type_class;

                $rule_condition_type_instance->data_log = &$data_item;

                if (class_exists($rule_condition_type_class)
                    && (
                        (
                            $rule_condition->ruleConditionType->report_source == 1
                            && count($redtrack_data)
                            && $rule_condition_type_instance->check($redtrack_data, $rule_condition)
                        )
                        ||
                        (
                            $rule_condition->ruleConditionType->report_source == 2
                            && count($performance_data)
                            && $rule_condition_type_instance->check($performance_data, $rule_condition)
                        )
                    )
                ) {
                    echo ' | OK', "\n";
                    $data_item['passed'] = true;
                    continue;
                }

                $is_adapt = false;
                echo ' | NO', "\n";
                $data_item['passed'] = false;
                break;
            }

            $data_items['items'][] = $data_item;

            if ($is_adapt) {
                $data[] = $data_items;
                return true;
            }
        }

        $data[] = $data_items;
        return false;
    }

    private function sendNotify()
    {
        $this->log->data = $this->log->data_text;

        Mail::send('emails.campaign_rule_passed', $this->log->toArray(), function ($message) {
            $message->to([
                env('MAIL_CAMPAIGN_RULE_PASSED_TO')
            ])->subject('Campaign Rule Passed');
            $message->from(env('MAIL_CAMPAIGN_RULE_PASSED_FROM'));
        });
    }
}
