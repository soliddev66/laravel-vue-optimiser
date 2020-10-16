@extends('adminlte::page')

@section('title', 'Traffic Sources')

@section('content_header')
    <h1>Traffic Sources</h1>
@stop

@section('content')
    <traffic-sources :providers="{{ auth()->user()->providers }}"></traffic-sources>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#trafficSourcesTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true,
                "stateSave": true,
                "autoWidth": true,
                "responsive": true,
            });
        });
    </script>
@stop