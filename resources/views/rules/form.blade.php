@extends('adminlte::page')

@section('title', 'Rule ' . ucfirst(request()->route()->getActionMethod()))

@section('content_header')
    <h1>Rule {{ ucfirst(request()->route()->getActionMethod()) }}</h1>
@stop

@section('content')
    <rule-creator :rule="{{ json_encode($rule) }}"
        :rule-actions="{{ json_encode($rule_actions ?? null) }}"
        :rule-campaigns="{{ json_encode($rule_campaigns ?? null) }}"
        :rule-conditions="{{ json_encode($rule_conditions ?? null) }}"
        :rule-groups="{{ json_encode($rule_groups ?? null) }}"
        :rule-condition-type-groups="{{ json_encode($rule_condition_type_groups ?? null) }}"
        :rule-data-from-options="{{ json_encode($rule_data_from_options ?? null) }}"
        :action="'{{ request()->route()->getActionMethod() }}'">
    </rule-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop