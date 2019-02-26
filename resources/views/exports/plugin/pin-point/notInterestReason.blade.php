<table>
    <thead>
    <tr>
        <th></th>
        <th colspan="{{ sizeof($reasons) }}">Not Interest Reasons</th>
    </tr>
    <tr>
        <th>Week</th>
        @foreach($reasons as $reason)
            <th>{{ $reason }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($notInterestReasons as $notInterestReason)
        <tr>
            <td>{{ $notInterestReason->week }}</td>
            @foreach ($notInterestReason->reasons as $item)
                <td>{{ !empty($item->total) ? $item->total : 0 }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
