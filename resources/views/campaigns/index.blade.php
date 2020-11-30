@extends('adminlte::page')

@section('title', 'Campaigns')

@section('content_header')
    <h1>Campaigns</h1>
@stop

@section('content')
    <campaigns :providers="{{ App\Models\Provider::all() }}" :trackers="{{ App\Models\Tracker::all() }}" :accounts="{{ auth()->user()->providers }}" :campaigns="{{ json_encode($campaigns) }}"></campaigns>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // $('#campaignsTable').DataTable({
            //     retrieve: true,
            //     ordering: true,
            //     autoWidth: false,
            //     responsive: true,
            //     footerCallback: function(row, data, start, end, display) {
            //         var api = this.api(),
            //             data;
            //         // Remove the formatting to get integer data for summation
            //         var intVal = function(i) {
            //             return typeof i === 'string' ?
            //                 i.replace(/[\$,]/g, '') * 1 :
            //                 typeof i === 'number' ?
            //                 i : 0;
            //         };
            //         // Total over all pages
            //         totalBudget = api
            //             .column(8)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalPayout = api
            //             .column(10)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalClick = api
            //             .column(11)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalLpView = api
            //             .column(12)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalLpClick = api
            //             .column(13)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalConversion = api
            //             .column(14)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalAction = api
            //             .column(15)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalRevenue = api
            //             .column(18)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalCost = api
            //             .column(19)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         totalProfit = api
            //             .column(20)
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         // Total over this page
            //         pageTotal = api
            //             .column(8, { page: 'current' })
            //             .data()
            //             .reduce(function(a, b) {
            //                 return intVal(a) + intVal(b);
            //             }, 0);
            //         // Update footer
            //         $(api.column(6).footer()).html(
            //             'Total: ' + this.fnSettings().fnRecordsTotal()
            //         );
            //         $(api.column(8).footer()).html(
            //             totalBudget
            //         );
            //         $(api.column(10).footer()).html(
            //             totalPayout
            //         );
            //         $(api.column(11).footer()).html(
            //             totalClick
            //         );
            //         $(api.column(12).footer()).html(
            //             totalLpView
            //         );
            //         $(api.column(13).footer()).html(
            //             totalLpClick
            //         );
            //         $(api.column(14).footer()).html(
            //             totalConversion
            //         );
            //         $(api.column(15).footer()).html(
            //             totalAction
            //         );
            //         $(api.column(18).footer()).html(
            //             totalRevenue
            //         );
            //         $(api.column(19).footer()).html(
            //             totalCost
            //         );
            //         $(api.column(20).footer()).html(
            //             _.round(totalProfit, 2)
            //         );
            //     }
            // });
        });
    </script>
@stop