@extends('adminlte::page')

@section('title', 'Queue')

@section('content_header')
    <h1>Queue</h1>
@stop

@section('content')
    <queues :queues="{{ json_encode($queues) }}" :failed="{{ json_encode($failed_queues) }}"></queues>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#queuesTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
            });
        });
    </script>
@stop