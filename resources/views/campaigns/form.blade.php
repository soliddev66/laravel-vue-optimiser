@extends('adminlte::page')

@section('title', 'Campaign ' . ucfirst(request()->route()->getActionMethod()))

@section('content_header')
    <h1>Campaign {{ ucfirst(request()->route()->getActionMethod()) }}</h1>
@stop

@section('content')
    <campaign-creator
        :instance="{{ json_encode($instance ?? null) }}"
        :action="'{{ request()->route()->getActionMethod() }}'"
        :providers="{{ App\Models\Provider::all() }}"
        :trackers="{{ App\Models\Tracker::all() }}">
    </campaign-creator>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
@stop