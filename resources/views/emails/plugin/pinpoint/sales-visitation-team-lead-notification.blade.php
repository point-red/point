@extends('emails.template')

@section('content')
    <div class="body-text">
        There is {{ $totalVisitation }} visitation from your sales on {{ date('d F Y', strtotime($date)) }}, please check your sales report <a href="//{{ $projectCode . '.cloud.point.red' }}">here</a>
    </div>
@stop
