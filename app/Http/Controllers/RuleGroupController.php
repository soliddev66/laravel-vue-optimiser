<?php

namespace App\Http\Controllers;

use App\Models\RuleGroup;

use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255'
        ]);

        if ($validator->fails()) {
            return [
                'errors' => $validator->errors()->all()
            ];
        }

        RuleGroup::firstOrNew([
            'name' => request('name'),
            'user_id' => auth()->id()
        ])->save();

        return [];
    }

    public function selectionData()
    {
        return auth()->user()->ruleGroups;
    }
}