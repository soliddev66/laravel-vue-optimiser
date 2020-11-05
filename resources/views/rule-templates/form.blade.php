@extends('adminlte::page')

@section('title', 'Rule Template ' . ucfirst(request()->route()->getActionMethod()))

@section('content_header')
    <h1>Rule Template {{ ucfirst(request()->route()->getActionMethod()) }}</h1>
@stop

@section('content')
    <rule-template-creator :rule="{{ json_encode($rule ?? null) }}"
        :rule-conditions="{{ json_encode($rule_conditions ?? null) }}"
        :rule-condition-type-groups="{{ json_encode($rule_condition_type_groups ?? null) }}"
        :rule-data-from-options="{{ json_encode($rule_data_from_options ?? null) }}"
        :action="'{{ request()->route()->getActionMethod() }}'">
    </rule-template-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop