<?php

namespace App\Http\Controllers;

use DB;
use Exception;

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
        return view('rule-templates.index', compact('rules'));
    }

    private function loadFormData()
    {
        $rule_condition_type_groups = RuleConditionTypeGroup::all();

        foreach ($rule_condition_type_groups as $rule_condition_type_group) {
            $rule_condition_type_group->options = $rule_condition_type_group->ruleConditionTypes;
        }

        return [
            'rule_data_from_options' => RuleDataFromOption::all(),
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

        $ruleConditions = [];

        foreach ($rule->ruleConditionGroups as $ruleConditionGroup) {
            $ruleConditions[] = $ruleConditionGroup->ruleConditions;
        }

        $data['rule'] = $rule;
        $data['rule_conditions'] = $ruleConditions;

        return view('rule-templates.form', $data);
    }

    public function update(RuleTemplate $rule)
    {
        $validatedData = $this->validateRequest();

        DB::beginTransaction();

        try {
            $this->deleteRelations($rule);

            $rule->name = $validatedData['ruleName'];
            $rule->from = $validatedData['dataFrom'];
            $rule->exclude_day = $validatedData['excludedDay'];
            $rule->run_type = $validatedData['ruleRunType'];
            $rule->interval_amount = $validatedData['ruleIntervalAmount'];
            $rule->interval_unit = $validatedData['ruleIntervalUnit'];

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
        foreach (request('ruleConditions') as $ruleConditions) {
            $ruleConditionGroup = new RuleConditionGroupTemplate([
                'rule_template_id' => $rule->id
            ]);

            $ruleConditionGroup->save();

            foreach ($ruleConditions as $ruleCondition) {
                (new RuleConditionTemplate([
                    'rule_condition_group_template_id' => $ruleConditionGroup->id,
                    'rule_condition_type_id' => $ruleCondition['rule_condition_type_id'],
                    'operation' => $ruleCondition['operation'],
                    'amount' => $ruleCondition['amount'],
                    'unit' => $ruleCondition['unit']
                ]))->save();
            }
        }
    }

    private function validateRequest()
    {
        return request()->validate([
            'ruleName' => 'required|max:255',
            'dataFrom' => 'required',
            'excludedDay' => 'required',
            'ruleConditions' => 'required|present|array',
            'ruleConditions.*' => 'required|present|array',
            'ruleConditions.*.*.rule_condition_type_id' => 'required|exists:App\Models\RuleConditionType,id',
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
        $validatedData = $this->validateRequest();

        DB::beginTransaction();

        try {
            $rule = new RuleTemplate([
                'name' => $validatedData['ruleName'],
                'from' => $validatedData['dataFrom'],
                'exclude_day' => $validatedData['excludedDay'],
                'run_type' => $validatedData['ruleRunType'],
                'interval_amount' => $validatedData['ruleIntervalAmount'],
                'interval_unit' => $validatedData['ruleIntervalUnit'],
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
        foreach ($rule->ruleConditionGroups as $ruleConditionGroup) {
            $ruleConditionGroup->ruleConditions()->delete();
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