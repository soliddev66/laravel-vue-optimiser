@extends('adminlte::page')

@section('title', 'Rule Templates')

@section('content_header')
    <h1>Rule Templates</h1>
@stop

@section('content')
    <rule-templates :rules="{{ json_encode($rules) }}"></rule-templates>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#ruleTemplatesTable').DataTable({
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