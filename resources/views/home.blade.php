@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <dashboard :providers="{{ \App\Models\Provider::all() }}"></dashboard>
@stop

@section('css')
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop