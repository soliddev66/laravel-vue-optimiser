<?php

namespace App\Http\Controllers;

use App\Models\Rule;

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
            'rule_groups' => auth()->user()->ruleGroups
        ];
    }

    public function create()
    {
        return view('rules.form', $this->loadFormData());
    }
}