@extends('adminlte::page')

@section('title', 'Campaign ' . $campaign->name)

@section('content_header')
    <h1>Campaign {{ $campaign->name }}</h1>
@stop

@section('content')
    <campaign :campaign="{{ json_encode($campaign) }}" :groups="{{ json_encode($ad_groups) }}" :ads="{{ json_encode($ads) }}"></campaign>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#adGroupsTable, #adGroupsTable').DataTable({
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