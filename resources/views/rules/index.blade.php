@extends('adminlte::page')

@section('title', 'Rules')

@section('content_header')
    <h1>Rules</h1>
@stop

@section('content')
    <rules :rules="{{ json_encode($rules) }}" :rule-actions="{{ json_encode($rule_actions) }}"></rules>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#rulesTable').DataTable({
                "paging": true,
                "ordering": true,
                "info": true,
                "stateSave": true,
                "autoWidth": false,
                "responsive": false,
                "footerCallback": function(row, data, start, end, display) {

                }
            });
        });
    </script>
@stop