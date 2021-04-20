@extends('adminlte::page')

@section('title', 'Creative Sets')

@section('content_header')
    <h1>Creative Sets</h1>
@stop

@section('content')
    <creative-sets :creative-sets="{{ json_encode($creativeSets) }}"></creative-sets>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#creativeSetsTable').DataTable({
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