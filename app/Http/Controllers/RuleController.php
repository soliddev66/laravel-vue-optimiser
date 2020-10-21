<?php

namespace App\Http\Controllers;

class RuleController extends Controller
{
    public function index()
    {
        return view('rules.index');
    }
}