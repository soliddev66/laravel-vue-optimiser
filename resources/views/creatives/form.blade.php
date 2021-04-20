@extends('adminlte::page')

@section('title', 'Rule ' . ucfirst(request()->route()->getActionMethod()))

@section('content_header')
    <h1>Rule {{ ucfirst(request()->route()->getActionMethod()) }}</h1>
@stop

@section('content')
    <creative-creator :action="'{{ request()->route()->getActionMethod() }}'">
    </creative-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop