@extends('emails.template')

@section('content')
    <div class="title">===== Report =====</div>
    <br>
    <div class="body-text">
    	<b>Date</b> : {{ $day_time }}
        <br>
        <b>Branch</b> : {{ $project_name }}
    	<br>
    	<b>Sales</b> : {{ $sales_name }}
    	<br>
    	<b>Call</b> : {{ $call }}
    	<br>
    	<b>EC</b> : {{ $effective_call }}
    	<br>
		<b>Value</b> : {{ $value }}
		<br>

    	@if(count($items) == 0)
		   No Sales
		   <br>
		@else
		    @foreach($items as $item)
			    Sales {{ $item->item_name }} = {{ number_format($item->quantity, 0) }} pcs
			    <br>
		    @endforeach
		@endif
    </div>
@stop
