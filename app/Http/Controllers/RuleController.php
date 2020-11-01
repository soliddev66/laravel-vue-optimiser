<?php

namespace App\Http\Controllers;

use App\Models\Rule;

class RuleController extends Controller
{
    public function index()
    {
        $rules = Rule::get();
        return view('rules.index', compact('rules'));
    }

    public function create()
    {
        return view('rules.form');
    }
}