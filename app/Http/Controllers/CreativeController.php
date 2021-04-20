<?php

namespace App\Http\Controllers;

class CreativeController extends Controller
{
    public function index()
    {
        $creativeSets = auth()->user()->creativeSets()->get();

        return view('creatives.index', [
            'creativeSets' => $creativeSets
        ]);
    }

    public function create()
    {
        $type = request('type') ?? 'media';

        return view('creatives.form', [
            'type' => $type
        ]);
    }
}
