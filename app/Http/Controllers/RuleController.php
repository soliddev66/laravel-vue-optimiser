<?php

namespace App\Http\Controllers;

use DB;
use Exception;

use App\Models\Rule;
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
        return view('rules.index', compact('rules'));
    }

    private function loadFormData($rule)
    {
        $ruleConditions = [];

        foreach ($rule->ruleConditionGroups as $ruleConditionGroup) {
            $ruleConditions[] = $ruleConditionGroup->ruleConditions;
        }

        $rule_condition_type_groups = RuleConditionTypeGroup::all();

        foreach ($rule_condition_type_groups as $rule_condition_type_group) {
            $rule_condition_type_group->options = $rule_condition_type_group->ruleConditionTypes;
        }

        return [
            'rule' => $rule,
            'rule_data_from_options' => RuleDataFromOption::all(),
            'rule_campaigns' => $rule->campaigns ?? null,
            'rule_conditions' => $ruleConditions,
            'rule_groups' => auth()->user()->ruleGroups,
            'rule_condition_type_groups' => $rule_condition_type_groups,
            'campaigns' => auth()->user()->campaigns
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
        if ($rule->user_id !== auth()->id()) {
            return view('error', [
                'title' => 'There is no rule was found. Please contact Administrator for this case.'
            ]);
        }

        return view('rules.form', $this->loadFormData($rule));
    }

    public function update(Rule $rule)
    {
        if ($rule->user_id !== auth()->id()) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        $validatedData = $this->validateRequest();

        DB::beginTransaction();

        try {
            $this->deleteRelations($rule);

            $rule->name = $validatedData['ruleName'];
            $rule->rule_group_id = $validatedData['ruleGroup'];
            $rule->from = $validatedData['dataFrom'];
            $rule->exclude_day = $validatedData['excludedDay'];
            $rule->run_type = $validatedData['ruleRunType'];
            $rule->interval_amount = $validatedData['ruleIntervalAmount'];
            $rule->interval_unit = $validatedData['ruleIntervalUnit'];

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
        foreach (request('ruleConditions') as $ruleConditions) {
            $ruleConditionGroup = new RuleConditionGroup([
                'rule_id' => $rule->id
            ]);

            $ruleConditionGroup->save();

            foreach ($ruleConditions as $ruleCondition) {
                (new RuleCondition([
                    'rule_condition_group_id' => $ruleConditionGroup->id,
                    'rule_condition_type_id' => $ruleCondition['rule_condition_type_id'],
                    'operation' => $ruleCondition['operation'],
                    'amount' => $ruleCondition['amount'],
                    'unit' => $ruleCondition['unit']
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
            'ruleGroup' => 'required|exists:App\Models\RuleGroup,id',
            'dataFrom' => 'required',
            'excludedDay' => 'required',
            'ruleConditions' => 'required|present|array',
            'ruleConditions.*' => 'required|present|array',
            'ruleConditions.*.*.rule_condition_type_id' => 'required|exists:App\Models\RuleConditionType,id',
            'ruleConditions.*.*.operation' => 'required',
            'ruleConditions.*.*.amount' => 'required',
            'ruleConditions.*.*.unit' => 'required',
            'ruleCampaigns' => 'required|present|array',
            'ruleCampaigns.*' => 'exists:App\Models\Campaign,id',
            'ruleIntervalAmount' => 'required|numeric',
            'ruleIntervalUnit' => 'required',
            'ruleRunType' => 'required'
        ]);
    }

    public function store()
    {
        $validatedData = $this->validateRequest();

        DB::beginTransaction();

        try {
            $rule = new Rule([
                'name' => $validatedData['ruleName'],
                'rule_group_id' => $validatedData['ruleGroup'],
                'from' => $validatedData['dataFrom'],
                'exclude_day' => $validatedData['excludedDay'],
                'run_type' => $validatedData['ruleRunType'],
                'interval_amount' => $validatedData['ruleIntervalAmount'],
                'interval_unit' => $validatedData['ruleIntervalUnit'],
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
        if ($rule->user_id !== auth()->id()) {
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
        foreach ($rule->ruleConditionGroups as $ruleConditionGroup) {
            $ruleConditionGroup->ruleConditions()->delete();
        }
        $rule->ruleConditionGroups()->delete();
        $rule->campaigns()->detach();
    }

    public function status(Rule $rule)
    {
        if ($rule->user_id !== auth()->id()) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        $rule->status = $rule->status == Rule::STATUS_ACTIVE ? Rule::STATUS_PAUSED : Rule::STATUS_ACTIVE;
        $rule->save();

        return [];
    }
}