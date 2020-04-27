<table>
    <thead>
    <tr>
        <th></th>
        <th colspan="{{ sizeof($reasons) }}">No Interest Reasons</th>
    </tr>
    <tr>
        <th>Week</th>
        @foreach($reasons as $reason)
            <th>{{ $reason }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($noInterestReasons as $noInterestReason)
        <tr>
            <td>{{ $noInterestReason->week }}</td>
            @foreach ($noInterestReason->reasons as $item)
                <td>{{ !empty($item->total) ? $item->total : 0 }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
