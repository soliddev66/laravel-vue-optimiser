@extends('adminlte::page')

@section('title', 'Campaign ' . $campaign->name)

@section('content_header')
    <h1>Campaign {{ $campaign->name }}</h1>
@stop

@section('content')
    <campaign :trackers="{{ App\Models\Tracker::all() }}" :campaign="{{ json_encode($campaign) }}"></campaign>
@stop

@section('css')
@stop

@section('js')
@stop