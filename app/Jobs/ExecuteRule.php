<?php

namespace App\Jobs;

use App\Mail\CampaignRulePassed;
use App\Models\Campaign;
use App\Models\Rule;
use App\Models\RuleLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class ExecuteRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $rule_id;
    protected $log;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rule_id)
    {
        $this->rule_id = $rule_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rule = Rule::find($this->rule_id);

        $time_range_class = 'App\\Utils\\TimeFrames\\' . $rule->timeRange->provider;

        $time_range = (new $time_range_class())->get();

        foreach ($rule->ruleRuleActions as $rule_rule_action) {
            $rule_campaigns = json_decode($rule_rule_action->action_data)->ruleCampaigns;

            foreach ($rule_campaigns as $rule_campaign) {
                if (is_object($rule_campaign)) {
                    $campaign = Campaign::find($rule_campaign->id);
                } else {
                    $campaign = Campaign::find($rule_campaign);
                }

                switch ($rule_rule_action->ruleAction->calculation_type) {
                    case 1:
                        $this->log = new RuleLog();
                        $this->log->rule_id = $this->rule_id;
                        $this->log->start_date = $time_range[0]->format('Y-m-d');
                        $this->log->end_date = $time_range[1]->format('Y-m-d');

                        $redtrack_data = $campaign->redtrackReport()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();

                        $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
                        $performance_data = (new $ad_vendor_class)->getPerformanceData($campaign, $time_range);

                        if ($this->checkConditions($campaign, $rule, $redtrack_data, $performance_data, 1)) {
                            $this->log->passed = true;

                            // ActivateCampaign, PauseCampaign, BlockWidgetsPushlisher, UnBlockWidgetsPushlisher, ChangeCampaignBudget, ChangeCampaignBid
                            $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule_rule_action->ruleAction->provider;

                            if (class_exists($rule_action_class)) {
                                if ($rule->run_type == 1) {
                                    (new $rule_action_class())->visual($campaign, $this->log->data_text, $rule_campaign->data ?? null);
                                }

                                if ($rule->run_type == 2 || $rule->run_type == 3) {
                                    (new $rule_action_class())->process($campaign, $this->log->data_text, $rule_campaign->data ?? null);
                                }

                                if ($rule->run_type == 1 || $rule->run_type == 3) {
                                    $this->sendNotify();
                                }
                            }

                            $this->log->data = json_encode($this->log->data_text);
                            $this->log->save();
                        }

                        break;

                    case 2:
                        $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
                        $redtrack_domain_data = (new $ad_vendor_class)->getDomainData($campaign, $time_range);

                        foreach ($redtrack_domain_data as $data) {
                            $this->log = new RuleLog();
                            $this->log->rule_id = $this->rule_id;
                            $this->log->start_date = $time_range[0]->format('Y-m-d');
                            $this->log->end_date = $time_range[1]->format('Y-m-d');

                            if ($this->checkConditions($campaign, $rule, [$data], [$data], 2)) {
                                $this->log->passed = true;

                                // BlockSite
                                $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule_rule_action->ruleAction->provider;

                                if (class_exists($rule_action_class)) {
                                    if ($rule->run_type == 1) {
                                        (new $rule_action_class())->visual($campaign, $data, $this->log->data_text);
                                    }

                                    if ($rule->run_type == 2 || $rule->run_type == 3) {
                                        (new $rule_action_class())->process($campaign, $data, $this->log->data_text);
                                    }

                                    if ($rule->run_type == 1 || $rule->run_type == 3) {
                                        $this->sendNotify();
                                    }
                                }

                                $this->log->data = json_encode($this->log->data_text);
                                $this->log->save();
                            }
                        }

                        break;

                    case 3:
                        foreach ($campaign->ads as $ad) {
                            $redtrack_content_stats = $ad->redtrackContentStats()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();

                            $this->log = new RuleLog();
                            $this->log->rule_id = $this->rule_id;
                            $this->log->start_date = $time_range[0]->format('Y-m-d');
                            $this->log->end_date = $time_range[1]->format('Y-m-d');

                            if ($this->checkConditions($campaign, $rule, $redtrack_content_stats, $redtrack_content_stats, 3)) {
                                $this->log->passed = true;

                                // PauseContents, ActivateContents
                                $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule_rule_action->ruleAction->provider;

                                if (class_exists($rule_action_class)) {
                                    if ($rule->run_type == 1) {
                                        (new $rule_action_class())->visual($campaign, $ad, $this->log->data_text);
                                    }

                                    if ($rule->run_type == 2 || $rule->run_type == 3) {
                                        (new $rule_action_class())->process($campaign, $ad, $this->log->data_text);
                                    }

                                    if ($rule->run_type == 1 || $rule->run_type == 3) {
                                        $this->sendNotify();
                                    }
                                }

                                $this->log->data = json_encode($this->log->data_text);
                                $this->log->save();
                            }
                        }

                        break;

                    case 4:
                        $this->log = new RuleLog();
                        $this->log->rule_id = $this->rule_id;
                        $this->log->start_date = $time_range[0]->format('Y-m-d');
                        $this->log->end_date = $time_range[1]->format('Y-m-d');

                        $redtrack_data = $campaign->redtrackReport()->whereBetween('date', [$time_range[0]->format('Y-m-d'), $time_range[1]->format('Y-m-d')])->get();

                        $ad_vendor_class = 'App\\Utils\\AdVendors\\' . ucfirst($campaign->provider->slug);
                        $redtrack_domain_data = (new $ad_vendor_class)->getDomainData($campaign, $time_range);

                        if ($this->checkConditions($campaign, $rule, $redtrack_data, $redtrack_domain_data, 4)) {
                            $this->log->passed = true;

                            // ChangePublisherBid
                            $rule_action_class = 'App\\Utils\\RuleActions\\' . $rule_rule_action->ruleAction->provider;

                            if (class_exists($rule_action_class)) {
                                if ($rule->run_type == 1) {
                                    (new $rule_action_class())->visual($campaign, $this->log->data_text, $rule_campaign->data ?? null);
                                }

                                if ($rule->run_type == 2 || $rule->run_type == 3) {
                                    (new $rule_action_class())->process($campaign, $this->log->data_text, $rule_campaign->data ?? null);
                                }

                                if ($rule->run_type == 1 || $rule->run_type == 3) {
                                    $this->sendNotify();
                                }
                            }

                            $this->log->data = json_encode($this->log->data_text);
                            $this->log->save();
                        }

                        break;
                }
            }
        }
    }

    private function checkConditions($campaign, $rule, $redtrack_data, $performance_data, $calculation_type)
    {
        $data = [];
        $this->log->data_text['info'] = &$data;

        foreach ($rule->ruleConditionGroups as $rule_condition_group) {
            $is_adapt = true;

            $data_items = [];

            $data_items['group'] = $rule_condition_group->id;
            $data_items['items'] = [];

            foreach ($rule_condition_group->ruleConditions as $rule_condition) {
                $data_item = [];
                $rule_condition_type_class = 'App\\Utils\\RuleConditionTypes\\' . $rule_condition->ruleConditionType->provider;

                $data_item['ruleConditionType'] = $rule_condition_type_class;

                $rule_condition_type_instance = new $rule_condition_type_class();

                $rule_condition_type_instance->data_log = &$data_item;

                if (class_exists($rule_condition_type_class)
                    && (
                        (
                            $rule_condition->ruleConditionType->report_source == 1
                            && count($redtrack_data)
                            && $rule_condition_type_instance->check($campaign, $redtrack_data, $rule_condition, $calculation_type)
                        )
                        ||
                        (
                            $rule_condition->ruleConditionType->report_source == 2
                            && count($performance_data)
                            && $rule_condition_type_instance->check($campaign, $performance_data, $rule_condition, $calculation_type)
                        )
                    )
                ) {
                    $data_item['passed'] = true;
                    $data_items['items'][] = $data_item;
                    continue;
                }

                $is_adapt = false;
                $data_item['passed'] = false;
                $data_items['items'][] = $data_item;
                break;
            }

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
        Mail::to(env('MAIL_CAMPAIGN_RULE_PASSED_TO'))->queue(new CampaignRulePassed($this->log->toArray()));
    }
}
