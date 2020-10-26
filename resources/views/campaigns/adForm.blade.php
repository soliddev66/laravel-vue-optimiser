@extends('adminlte::page')

@section('title', 'Ad create')

@section('content_header')
    <h1>Ad create</h1>
@stop

@section('content')
    <ad-creator :campaign="{{ json_encode($campaign) }}" :ad-group-id="'{{ $ad_group_id }}'"></ad-creator>
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop