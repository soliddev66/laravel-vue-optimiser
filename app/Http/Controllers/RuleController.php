<?php

namespace App\Http\Controllers;

use DB;
use Exception;

use Illuminate\Support\Facades\Gate;

use App\Models\Rule;
use App\Models\RuleAction;
use App\Models\RuleTemplate;
use App\Models\RuleCampaign;
use App\Models\RuleCondition;
use App\Models\RuleConditionType;
use App\Models\RuleDataFromOption;
use App\Models\RuleConditionGroup;
use App\Models\RuleConditionTypeGroup;

class RuleController extends Controller
{
    public function index()
    {
        $rules = auth()->user()->rules;

        $rule_actions = RuleAction::all();

        return view('rules.index', compact('rules', 'rule_actions'));
    }

    private function loadFormData($rule)
    {
        $rule_conditions = [];

        foreach ($rule->ruleConditionGroups as $rule_condition_group) {
            $rule_conditions[] = $rule_condition_group->ruleConditions;
        }

        $rule_condition_type_groups = RuleConditionTypeGroup::all();

        foreach ($rule_condition_type_groups as $rule_condition_type_group) {
            $rule_condition_type_group->options = $rule_condition_type_group->ruleConditionTypes;
        }

        $rule->rule_action_id = $rule->rule_action_id ?? null;
        $rule->rule_action_provider = $rule->ruleAction->provider ?? null;
        $rule->excluded_day_type = $rule->timeRange->excluded_day_type ?? null;

        if (request('action')) {
            $rule_action = RuleAction::find(request('action'));
            $rule->rule_action_id = $rule->rule_action_id ?? $rule_action->id;
            $rule->rule_action_provider = $rule->rule_action_provider ?? $rule_action->provider;
        }

        return [
            'rule' => $rule,
            'rule_actions' => RuleAction::all(),
            'rule_data_from_options' => RuleDataFromOption::all(),
            'rule_campaigns' => $rule->campaigns ?? null,
            'rule_conditions' => $rule_conditions,
            'rule_groups' => auth()->user()->ruleGroups,
            'rule_condition_type_groups' => $rule_condition_type_groups
        ];
    }

    public function data()
    {
        return response()->json([
            'rules' => auth()->user()->rules
        ]);
    }

    public function create(RuleTemplate $rule)
    {
        return view('rules.form', $this->loadFormData($rule));
    }

    public function edit(Rule $rule)
    {
        if (Gate::denies('modifiable', $rule)) {
            return view('error', [
                'title' => 'There is no rule was found. Please contact Administrator for this case.'
            ]);
        }

        return view('rules.form', $this->loadFormData($rule));
    }

    public function update(Rule $rule)
    {
        if (Gate::denies('modifiable', $rule)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        $validated_data = $this->validateRequest();

        DB::beginTransaction();

        try {
            $this->deleteRelations($rule);

            $rule->name = $validated_data['ruleName'];
            $rule->rule_group_id = $validated_data['ruleGroup'];
            $rule->rule_action_id = $validated_data['ruleAction'];
            $rule->from = $validated_data['dataFrom'];
            $rule->exclude_day = $validated_data['excludedDay'];
            $rule->run_type = $validated_data['ruleRunType'];
            $rule->interval_amount = $validated_data['ruleIntervalAmount'];
            $rule->interval_unit = $validated_data['ruleIntervalUnit'];
            $rule->widget = $validated_data['ruleWidget'];
            $rule->is_widget_included = $validated_data['ruleWidgetIncluded'];

            $rule->save();

            $this->createRuleConditions($rule);
            $this->createRuleCampaigns($rule);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => [$e->getMessage()]
            ], 500);
        }

        return [];
    }

    private function createRuleConditions($rule)
    {
        foreach (request('ruleConditions') as $rule_conditions) {
            $rule_condition_group = new RuleConditionGroup([
                'rule_id' => $rule->id
            ]);

            $rule_condition_group->save();

            foreach ($rule_conditions as $rule_condition) {
                (new RuleCondition([
                    'rule_condition_group_id' => $rule_condition_group->id,
                    'rule_condition_type_id' => $rule_condition['rule_condition_type_id'],
                    'operation' => $rule_condition['operation'],
                    'amount' => $rule_condition['amount'],
                    'unit' => $rule_condition['unit']
                ]))->save();
            }
        }
    }

    private function createRuleCampaigns($rule)
    {
        foreach (request('ruleCampaigns') as $campaign) {
            (new RuleCampaign([
                'rule_id' => $rule->id,
                'campaign_id' => $campaign
            ]))->save();
        }
    }

    private function validateRequest()
    {
        return request()->validate([
            'ruleName' => 'required|max:255',
            'ruleGroup' => 'required|exists:rule_groups,id',
            'ruleAction' => 'required|exists:rule_actions,id',
            'dataFrom' => 'required',
            'excludedDay' => 'required',
            'ruleWidget' => 'required',
            'ruleWidgetIncluded' => 'required',
            'ruleConditions' => 'required|present|array',
            'ruleConditions.*' => 'required|present|array',
            'ruleConditions.*.*.rule_condition_type_id' => 'required|exists:rule_condition_types,id',
            'ruleConditions.*.*.operation' => 'required',
            'ruleConditions.*.*.amount' => 'required',
            'ruleConditions.*.*.unit' => 'required',
            'ruleActionSubmitData' => 'required|present|array',
            'ruleIntervalAmount' => 'required|numeric',
            'ruleIntervalUnit' => 'required',
            'ruleRunType' => 'required'
        ]);
    }

    public function store()
    {
        $validated_data = $this->validateRequest();

        DB::beginTransaction();

        try {
            $rule = new Rule([
                'name' => $validated_data['ruleName'],
                'rule_group_id' => $validated_data['ruleGroup'],
                'rule_action_id' => $validated_data['ruleAction'],
                'from' => $validated_data['dataFrom'],
                'exclude_day' => $validated_data['excludedDay'],
                'widget' => $validated_data['ruleWidget'],
                'is_widget_included' => $validated_data['ruleWidgetIncluded'],
                'run_type' => $validated_data['ruleRunType'],
                'interval_amount' => $validated_data['ruleIntervalAmount'],
                'interval_unit' => $validated_data['ruleIntervalUnit'],
                'user_id' => auth()->id(),
                'status' => 'ACTIVE'
            ]);

            $rule->save();

            $this->createRuleConditions($rule);
            $this->createRuleCampaigns($rule);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => [$e->getMessage()]
            ], 500);
        }

        return [];
    }

    public function delete(Rule $rule)
    {
        if (Gate::denies('modifiable', $rule)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }
        DB::beginTransaction();

        try {
            $this->deleteRelations($rule);
            $rule->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => [$e->getMessage()]
            ], 500);
        }

        return [];
    }

    private function deleteRelations($rule)
    {
        foreach ($rule->ruleConditionGroups as $rule_condition_group) {
            $rule_condition_group->ruleConditions()->delete();
        }
        $rule->ruleConditionGroups()->delete();
        $rule->campaigns()->detach();
    }

    public function status(Rule $rule)
    {
        if (Gate::denies('modifiable', $rule)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        $rule->status = $rule->status == Rule::STATUS_ACTIVE ? Rule::STATUS_PAUSED : Rule::STATUS_ACTIVE;
        $rule->save();

        return [];
    }
}