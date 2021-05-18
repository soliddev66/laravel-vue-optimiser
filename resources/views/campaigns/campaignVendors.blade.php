@extends('adminlte::page')

@section('title', 'Campaign Vendors')

@section('content_header')
    <h1>Campaign Vendors</h1>
@stop

@section('content')
    <campaign-vendors
        :providers="{{ App\Models\Provider::all() }}">
    </campaign-vendors>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
@stop