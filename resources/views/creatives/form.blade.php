@extends('adminlte::page')

@section('title', 'Creative ' . ucfirst(request()->route()->getActionMethod()))

@section('content_header')
    <h1>Creative {{ ucfirst(request()->route()->getActionMethod()) }}</h1>
@stop

@section('content')
    <creative-set-creator type="{{ $type }}" :creative-set="{{ json_encode($creativeSet ?? null) }}" :action="'{{ request()->route()->getActionMethod() }}'">
    </creative-set-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop