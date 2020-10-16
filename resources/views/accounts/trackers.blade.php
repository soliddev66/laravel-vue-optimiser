@extends('adminlte::page')

@section('title', 'Trackers')

@section('content_header')
    <h1>Trackers</h1>
@stop

@section('content')
    <trackers :trackers="{{ auth()->user()->trackers }}"></trackers>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#trackersTable').DataTable({
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