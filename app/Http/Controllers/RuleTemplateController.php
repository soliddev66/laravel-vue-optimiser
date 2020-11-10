<?php

namespace App\Http\Controllers;

use DB;
use Exception;

use App\Models\RuleAction;
use App\Models\RuleTemplate;
use App\Models\RuleDataFromOption;
use App\Models\RuleConditionTemplate;
use App\Models\RuleConditionTypeGroup;
use App\Models\RuleConditionGroupTemplate;

class RuleTemplateController extends Controller
{
    public function index()
    {
        $rules = RuleTemplate::all();

        $rule_actions = RuleAction::all();

        return view('rule-templates.index', compact('rules', 'rule_actions'));
    }

    private function loadFormData()
    {
        $rule_condition_type_groups = RuleConditionTypeGroup::all();

        foreach ($rule_condition_type_groups as $rule_condition_type_group) {
            $rule_condition_type_group->options = $rule_condition_type_group->ruleConditionTypes;
        }

        return [
            'rule_actions' => RuleAction::all(),
            'rule_data_from_options' => RuleDataFromOption::all(),
            'rule_action_id' => request('action') ?? null,
            'rule_condition_type_groups' => $rule_condition_type_groups
        ];
    }

    public function data()
    {
        return response()->json([
            'rules' => RuleTemplate::all()
        ]);
    }

    public function create()
    {
        return view('rule-templates.form', $this->loadFormData());
    }

    public function edit(RuleTemplate $rule)
    {
        $data = $this->loadFormData();

        $rule_conditions = [];

        foreach ($rule->ruleConditionGroups as $rule_condition_group) {
            $rule_conditions[] = $rule_condition_group->ruleConditions;
        }

        $data['rule'] = $rule;
        $data['rule_conditions'] = $rule_conditions;

        return view('rule-templates.form', $data);
    }

    public function update(RuleTemplate $rule)
    {
        $validated_data = $this->validateRequest();

        DB::beginTransaction();

        try {
            $this->deleteRelations($rule);

            $rule->name = $validated_data['ruleName'];
            $rule->rule_action_id = $validated_data['ruleAction'];
            $rule->from = $validated_data['dataFrom'];
            $rule->exclude_day = $validated_data['excludedDay'];
            $rule->run_type = $validated_data['ruleRunType'];
            $rule->interval_amount = $validated_data['ruleIntervalAmount'];
            $rule->interval_unit = $validated_data['ruleIntervalUnit'];

            $rule->save();

            $this->createRuleConditions($rule);

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
            $rule_condition_group = new RuleConditionGroupTemplate([
                'rule_template_id' => $rule->id
            ]);

            $rule_condition_group->save();

            foreach ($rule_conditions as $rule_condition) {
                (new RuleConditionTemplate([
                    'rule_condition_group_template_id' => $rule_condition_group->id,
                    'rule_condition_type_id' => $rule_condition['rule_condition_type_id'],
                    'operation' => $rule_condition['operation'],
                    'amount' => $rule_condition['amount'],
                    'unit' => $rule_condition['unit']
                ]))->save();
            }
        }
    }

    private function validateRequest()
    {
        return request()->validate([
            'ruleName' => 'required|max:255',
            'ruleAction' => 'required|exists:rule_actions,id',
            'dataFrom' => 'required',
            'excludedDay' => 'required',
            'ruleConditions' => 'required|present|array',
            'ruleConditions.*' => 'required|present|array',
            'ruleConditions.*.*.rule_condition_type_id' => 'required|exists:rule_condition_types,id',
            'ruleConditions.*.*.operation' => 'required',
            'ruleConditions.*.*.amount' => 'required',
            'ruleConditions.*.*.unit' => 'required',
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
            $rule = new RuleTemplate([
                'name' => $validated_data['ruleName'],
                'from' => $validated_data['dataFrom'],
                'exclude_day' => $validated_data['excludedDay'],
                'rule_action_id' => $validated_data['ruleAction'],
                'run_type' => $validated_data['ruleRunType'],
                'interval_amount' => $validated_data['ruleIntervalAmount'],
                'interval_unit' => $validated_data['ruleIntervalUnit'],
                'status' => 'ACTIVE'
            ]);

            $rule->save();

            $this->createRuleConditions($rule);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => [$e->getMessage()]
            ], 500);
        }

        return [];
    }

    public function delete(RuleTemplate $rule)
    {
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
    }

    public function status(RuleTemplate $rule)
    {
        $rule->status = $rule->status == RuleTemplate::STATUS_ACTIVE ? RuleTemplate::STATUS_PAUSED : RuleTemplate::STATUS_ACTIVE;
        $rule->save();

        return [];
    }
}