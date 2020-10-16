@extends('adminlte::page')

@section('title', 'Campaigns')

@section('content_header')
    <h1>Campaigns</h1>
@stop

@section('content')
    <campaigns :campaigns="{{ json_encode($campaigns) }}"></campaigns>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#campaignsTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true,
                "stateSave": true,
                "autoWidth": false,
                "responsive": true,
            });
        });
    </script>
@stop