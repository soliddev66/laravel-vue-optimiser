<?php

namespace App\Http\Controllers;

use App\Models\RuleGroup;

use Illuminate\Support\Facades\Validator;

class RuleGroupController extends Controller
{
    public function index()
    {
    }

    private function loadFormData()
    {

    }

    public function create()
    {

    }

    public function store()
    {
        $validated_data = request()->validate([
            'name' => 'required|max:255'
        ]);

        RuleGroup::firstOrNew([
            'name' => $validated_data['name'],
            'user_id' => auth()->id()
        ])->save();

        return [];
    }

    public function selectionData()
    {
        return auth()->user()->ruleGroups;
    }
}