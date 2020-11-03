<?php

namespace App\Http\Controllers;

use DB;
use Exception;

use App\Models\Rule;
use App\Models\RuleCondition;
use App\Models\RuleConditionType;
use App\Models\RuleConditionGroup;

class RuleController extends Controller
{
    public function index()
    {
        $rules = auth()->user()->rules;
        return view('rules.index', compact('rules'));
    }

    private function loadFormData()
    {
        return [
            'rule_groups' => auth()->user()->ruleGroups,
            'rule_condition_types' => RuleConditionType::all(),
            'campaigns' => auth()->user()->campaigns
        ];
    }

    public function data()
    {
        return response()->json([
            'rules' => auth()->user()->rules
        ]);
    }

    public function create()
    {
        return view('rules.form', $this->loadFormData());
    }

    public function store()
    {
        $validatedData = request()->validate([
            'ruleName' => 'required|max:255',
            'ruleGroup' => 'required|exists:App\Models\RuleGroup,id',
            'dataFrom' => 'required',
            'excludedDay' => 'required',
            'ruleConditions' => 'required|present|array',
            'ruleConditions.*' => 'required|present|array',
            'ruleConditions.*.*.type' => 'required|exists:App\Models\RuleConditionType,id',
            'ruleConditions.*.*.operation' => 'required',
            'ruleConditions.*.*.amount' => 'required',
            'ruleConditions.*.*.unit' => 'required',
            'ruleCampaigns' => 'required|present|array',
            'ruleCampaigns.*' => 'exists:App\Models\Campaign,id',
            'ruleIntervalAmount' => 'required|numeric',
            'ruleIntervalUnit' => 'required',
            'ruleRunType' => 'required'
        ]);

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

            foreach (request('ruleConditions') as $ruleConditions) {
                $ruleConditionGroup = new RuleConditionGroup([
                    'rule_id' => $rule->id
                ]);

                $ruleConditionGroup->save();

                foreach ($ruleConditions as $ruleCondition) {
                    (new RuleCondition([
                        'rule_condition_group_id' => $ruleConditionGroup->id,
                        'rule_condition_type_id' => $ruleCondition['type'],
                        'operation' => $ruleCondition['operation'],
                        'amount' => $ruleCondition['amount'],
                        'unit' => $ruleCondition['unit']
                    ]))->save();
                }
            }

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
        DB::beginTransaction();

        try {
            foreach ($rule->ruleConditionGroups() as $ruleConditionGroup) {
                $ruleConditionGroup->ruleConditions()->delete();
            }
            $rule->ruleConditionGroups()->delete();
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
}