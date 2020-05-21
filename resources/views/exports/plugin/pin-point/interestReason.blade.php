<table>
    <thead>
    <tr>
        <th></th>
        <th colspan="{{ sizeof($reasons) }}">Interest Reasons</th>
    </tr>
    <tr>
        <th>Week</th>
        @foreach($reasons as $reason)
            <th>{{ $reason }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($interestReasons as $interestReason)
        <tr>
            <td>{{ $interestReason->week }}</td>
            @foreach ($interestReason->reasons as $item)
                <td>{{ !empty($item->total) ? $item->total : 0 }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
