@extends('emails.template')

@section('content')
    <div class="title">===== Report =====</div>
    <br>
    <div class="body-text">
    	<b>Hari/Tanggal</b> : {{ $day_time }}
        <br>
        <b>Area</b> : {{ $project_name }}
    	<br>
    	<b>Nama Sales</b> : {{ $sales_name }}
    	<br>
    	<b>Call</b> : {{ $call }}
    	<br>
    	<b>EC</b> : {{ $effective_call }}
    	<br>

    	@if(count($items) == 0)
		   Tidak ada Penjualan
		   <br>
		@else
		    @foreach($items as $item)
			    Penjualan {{ $item->item_name }} = {{ number_format($item->quantity, 0) }}pcs
			    <br>
		    @endforeach
		@endif

    	<b>Value</b> : {{ $value }}
    </div>
@stop
