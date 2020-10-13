@extends('adminlte::page')

@section('title', 'Campaign ' . ucfirst(request()->route()->getActionMethod()))

@section('content_header')
    <h1>Campaign {{ ucfirst(request()->route()->getActionMethod()) }}</h1>
@stop

@section('content')
    <campaign-creator
        :instance="{{ json_encode($instance ?? '[]') }}"
        :provider="{{ json_encode($provider ?? '[]') }}"
        :action="{{ request()->route()->getActionMethod() }}"
        :providers="{{ App\Models\Provider::all() }}"
        :trackers="{{ App\Models\Tracker::all() }}"
        :accounts="{{ auth()->user()->providers }}">
    </campaign-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop