<style>
	th {
		font-family: 'Helvetica';
		font-weight: normal;
		font-style: normal;
		font-variant: normal;
		font-size: 10;
	}
	td {
		font-family: 'Helvetica';
		font-weight: normal;
		font-style: normal;
		font-variant: normal;
		font-size: 8;
	
	}
</style>
<table width="100%">
  <tr>
    <th width="77%" style="text-align:left;vertical-align:top;">
      @if ($logo)
        <img src="{{$logo->public_url}}" width="50"/>
      @endif
    </th>
    <th style="text-align:left;">
      <span style="font-size: 16px;font-weight: bold;">Cut Off</span><br/>
      <span style="font-size: 13px;font-weight: normal;">{{ $tenant }}</span><br/>
      <span style="font-size: 13px;font-weight: normal;">{{ $address ?? '-' }}</span><br/>
      <span style="font-size: 13px;font-weight: normal;">{{ $phone ?? '-' }}</span>
    </th>
  </tr>
</table>

<table width="100%" border="1" style="border-collapse: collapse; border: 1px solid #000;" cellspacing="4">
    <thead>
    	<tr>
	        <th width="7%">
	        	No
	        </th>
	        <th width="14%">Account Number</th>
	        <th width="43%">Account Name</th>
	        <th width="15%">Debit</th>
	        <th width="15%">Credit</th>
	    </tr>
	</thead>
	<tbody>
    @foreach($data as $key => $cutoff)
        <tr>
            <td style="text-align:center">{{ ($key + 1) }}</td>
            <td>{{ $cutoff->chartOfAccount->number }}</td>
            <td>{{ $cutoff->chartOfAccount->alias }}</td>
            <td style="text-align:right;">{{ number_format($cutoff->debit, 0) }}</td>
            <td  style="text-align:right">{{ number_format($cutoff->credit, 0) }}</td>
        </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr>
      <th colspan="3" style="text-align:right">
        Total
      </th>
      <th style="text-align:right"> {{ number_format($data->sum(function($row) { return $row->debit; }), 0) }}</th>
      <th style="text-align:right"> {{ number_format($data->sum(function($row) { return $row->credit; }), 0) }}</th>
    </tr>
  </tfoot>
</table>
