@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <dashboard
        :data1="{{ json_encode($summary_data) }}"
        :data2="{{ json_encode($data_by_date) }}"
        :providers="{{ \App\Models\Provider::all() }}"
    ></dashboard>
@stop

@section('css')
@stop

@section('js')
    <script> console.log('Hi!'); </script>
@stop