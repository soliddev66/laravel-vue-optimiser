<?php

namespace App\Http\Controllers;

use App\Models\RuleGroup;
use App\Vngodev\ResourceImporter;

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

        $resource_importer = new ResourceImporter();

        $resource_importer->insertOrUpdate('rule_groups', [[
            'name' => $validated_data['name'],
            'user_id' => auth()->id()
        ]], ['user_id']);

        return [];
    }

    public function selectionData()
    {
        return auth()->user()->ruleGroups;
    }
}