@extends('adminlte::page')

@section('title', 'Ad ' . $ad['title'])

@section('content_header')
    <h1>Ad {{ $ad['title'] }}</h1>
@stop

@section('content')
    <ad :ad="{{ json_encode($ad) }}"></ad>
@stop

@section('css')
@stop

@section('js')
@stop