@extends('emails.template')

@section('content')
    <div class="title">===== Report =====</div>
    <br>
    <div class="body-text">
    	<b>Date</b> : {{ $day_time }}
        <br>
        <b>Branch</b> : {{ $project_name }}
        <br>

        @foreach($user_data as $data)
            <br>
            ==================
            <br>
            <br>
            <b>Sales</b> : {{ $data['sales_name'] }}
            <br>
            <b>Call</b> : {{ $data['call'] }}
            <br>
            <b>EC</b> : {{ $data['effective_call'] }}
            <br>
            <b>Value</b> : {{ $data['value'] }}
            <br>

            @if(count($data['items']) == 0)
               No Sales
               <br>
            @else
                @foreach($data['items'] as $item)
                    Sales {{ $item->item_name }} = {{ number_format($item->quantity, 0) }} pcs
                    <br>
                @endforeach
            @endif
        @endforeach
    </div>
@stop
