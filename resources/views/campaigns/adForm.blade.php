@extends('adminlte::page')

@section('title', 'Ad create')

@section('content_header')
    <h1>Ad create</h1>
@stop

@section('content')
    @if (isset($instance))
        <ad-creator :campaign="{{ json_encode($campaign) }}" :ad-group-id="'{{ $ad_group_id }}'" :ad="{{ json_encode($instance) }}"></ad-creator>
    @else
        <ad-creator :campaign="{{ json_encode($campaign) }}" :ad-group-id="'{{ $ad_group_id }}'"></ad-creator>
    @endif
@stop

@section('css')
@stop

@section('js')
    <script></script>
@stop