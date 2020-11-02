@extends('adminlte::page')

@section('title', 'Campaign ' . $campaign->name)

@section('content_header')
    <h1>Campaign {{ $campaign->name }}</h1>
@stop

@section('content')
    <campaign :providers="{{ App\Models\Provider::all() }}" :trackers="{{ App\Models\Tracker::all() }}" :accounts="{{ auth()->user()->providers }}" :campaign="{{ json_encode($campaign) }}"></campaign>
@stop

@section('css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const widgetsTable = $('#widgetsTable').DataTable({
                retrieve: true,
                processing: true,
                serverSide: true,
                pageLength: 50,
                ajax: `/campaigns/{{ $campaign->id }}/widgets/${window.moment().format('YYYY-MM-DD')}/${window.moment().format('YYYY-MM-DD')}/redtrack`,
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'actions', name: 'actions' },
                    { data: 'widget_id', name: 'widget_id' },
                    { data: 'bid_modifier', name: 'bid_modifier' },
                    { data: 'calc_cpc', name: 'calc_cpc' },
                    { data: 'average_cpc', name: 'average_cpc' },
                    { data: 'spend', name: 'spend' },
                    { data: 'tr_conv', name: 'tr_conv' },
                    { data: 'tr_rev', name: 'tr_rev' },
                    { data: 'tr_net', name: 'tr_net' },
                    { data: 'tr_roi', name: 'tr_roi' },
                    { data: 'tr_epc', name: 'tr_epc' },
                    { data: 'epc', name: 'epc' },
                    { data: 'tr_cpa', name: 'tr_cpa' },
                    { data: 'impressions', name: 'impressions' },
                    { data: 'ts_clicks', name: 'ts_clicks' },
                    { data: 'trk_clicks', name: 'trk_clicks' },
                    { data: 'lp_clicks', name: 'lp_clicks' },
                    { data: 'lp_ctr', name: 'lp_ctr' },
                    { data: 'ctr', name: 'ctr' },
                    { data: 'tr_cvr', name: 'tr_cvr' },
                    { data: 'ecpm', name: 'ecpm' },
                    { data: 'lp_cr', name: 'lp_cr' },
                    { data: 'lp_cpc', name: 'lp_cpc' },
                ]
            });
            $(document).on('change', '#provider, #account, #tracker, #targetDate-input', function(e) {
                const start = $('#targetDate-input').val().split(' - ')[0].trim();
                const end = $('#targetDate-input').val().split(' - ')[1].trim() === '...' ? '' : $('#targetDate-input').val().split(' - ')[1].trim();
                const tracker = $('#tracker').val();
                widgetsTable.ajax.url(`/campaigns/{{ $campaign->id }}/widgets/${start}/${end}/${tracker}`).load();
            });
        });
    </script>
@stop