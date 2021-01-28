@extends('adminlte::page')

@section('title', 'Rule Logs')

@section('content_header')
    <h1>Rule Logs</h1>
@stop

@section('content')
    <rule-logs :rule="{{ json_encode($rule) }}"></rule-logs>
@stop

@section('css')
@stop

@section('js')
@stop