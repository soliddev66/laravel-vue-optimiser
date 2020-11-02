<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use App\Models\RuleConditionType;

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

    public function create()
    {
        return view('rules.form', $this->loadFormData());
    }

    public function store()
    {

    }
}