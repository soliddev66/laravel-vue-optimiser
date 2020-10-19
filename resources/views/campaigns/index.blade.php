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
                "responsive": false,
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api(),
                        data;
                    // Remove the formatting to get integer data for summation
                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };
                    // Total over all pages
                    total = api
                        .column(8)
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    // Total over this page
                    pageTotal = api
                        .column(8, { page: 'current' })
                        .data()
                        .reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    // Update footer
                    $(api.column(6).footer()).html(
                        'Total: ' + this.fnSettings().fnRecordsTotal()
                    );
                    $(api.column(8).footer()).html(
                        total
                    );
                }
            });
        });
    </script>
@stop