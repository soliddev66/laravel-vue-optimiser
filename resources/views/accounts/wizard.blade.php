@extends('adminlte::page')

@section('title', 'Account Wizard')

@section('content_header')
    <h1>Account Wizard</h1>
@stop

@section('content')
    <account-wizard :providers="{{ $providers }}" :trackers="{{ $trackers }}" :step="{{ request('step') }}"></account-wizard>
@stop

@section('css')
@stop

@section('js')
@stop