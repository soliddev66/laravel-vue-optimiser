@extends('adminlte::page')

@section('title', 'Campaign Creator')

@section('content_header')
    <h1>Campaign Creator</h1>
@stop

@section('content')
    <campaign-creator :providers="{{ App\Models\Provider::all() }}" :trackers="{{ App\Models\Tracker::all() }}" :accounts="{{ auth()->user()->providers }}"></campaign-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop