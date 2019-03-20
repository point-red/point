@extends('emails.template')

@section('content')
    <div class="title">{{ $projectName }}</div>
    <hr/>
    <div class="body-text">
        There is {{ $totalVisitation }} visitation from your sales on {{ date('d F Y', strtotime($date)) }},
        please check your sales report <a href="https://{{ $projectCode . '.cloud.point.red/plugin/pin-point/sales-visitation-form?date_from=' . date('Y-m-d', strtotime($date)) .'&date_to=' . date('Y-m-d', strtotime($date)) }}">here</a>
    </div>
@stop
