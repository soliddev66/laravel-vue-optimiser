<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Models\RuleAction;
use App\Models\RuleRuleAction;
use App\Models\RuleCampaign;
use App\Models\RuleCondition;
use App\Models\RuleConditionGroup;
use App\Models\RuleConditionType;
use App\Models\RuleConditionTypeGroup;
use App\Models\RuleDataFromOption;
use App\Models\RuleTemplate;
use DB;
use Exception;
use Illuminate\Support\Facades\Gate;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class RuleController extends Controller
{
    public function index()
    {
        $rules = auth()->user()->rules()->get();

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
        $rule->rule_action_data = $rule->action_data ?? null;
        $rule->excluded_day_type = $rule->timeRange->excluded_day_type ?? null;

        if (request('action')) {
            $rule_action = RuleAction::find(request('action'));
            $rule->rule_action_id = $rule->rule_action_id ?? $rule_action->id;
            $rule->rule_action_provider = $rule->rule_action_provider ?? $rule_action->provider;
        }

        return [
            'rule' => $rule,
            'rule_action_selections' => RuleAction::all(),
            'rule_data_from_options' => RuleDataFromOption::all(),
            'rule_conditions' => $rule_conditions,
            'rule_groups' => auth()->user()->ruleGroups,
            'rule_rule_actions' => $rule->ruleRuleActions,
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

    public function logs(Rule $rule)
    {
        return view('rules.logs', compact('rule'));
    }

    public function logsData(Rule $rule)
    {
        return new DataTableCollectionResource($rule->logs()->orderBy(request('column'), request('dir'))->paginate(request('length')));
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
            $rule->action_data = json_encode($validated_data['ruleActionSubmitData']);
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

    private function createRuleRuleActions($rule)
    {
        foreach (request('ruleActions') as $rule_action) {
            (new RuleRuleAction([
                'rule_id' => $rule->id,
                'rule_action_id' => $rule_action['selectedRuleAction'],
                'action_data' => json_encode($rule_action['ruleActionData']),
            ]))->save();
        }
    }

    private function validateRequest()
    {
        return request()->validate([
            'ruleName' => 'required|max:255',
            'ruleGroup' => 'required|exists:rule_groups,id',
            'ruleActions' => 'required|present|array',
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
            $rule = new Rule([
                'name' => $validated_data['ruleName'],
                'rule_group_id' => $validated_data['ruleGroup'],
                'from' => $validated_data['dataFrom'],
                'exclude_day' => $validated_data['excludedDay'],
                'run_type' => $validated_data['ruleRunType'],
                'interval_amount' => $validated_data['ruleIntervalAmount'],
                'interval_unit' => $validated_data['ruleIntervalUnit'],
                'user_id' => auth()->id(),
                'status' => 'ACTIVE'
            ]);

            $rule->save();

            $this->createRuleRuleActions($rule);
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
        $rule->ruleRuleActions()->delete();
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