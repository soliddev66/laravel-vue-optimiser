@extends('adminlte::page')

@section('title', 'Rule ' . ucfirst(request()->route()->getActionMethod()))

@section('content_header')
    <h1>Rule {{ ucfirst(request()->route()->getActionMethod()) }}</h1>
@stop

@section('content')
    <rule-creator>
    </rule-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop