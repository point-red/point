<table>
    <tr>
        <td>Date Export</td>
        <td>{{ date('d F Y') }}</td>
    </tr>
</table>

<table>
    <tr>
        <th></th>
        @foreach ($heading as $headings)
            <th> {{ $headings }} </th>
        @endforeach
    </tr>

    @foreach($item as $key => $items)
        <tr>
            <td></td>
            @foreach($items as $itemData)
                <td>{{$itemData}}</td>
            @endforeach
        </tr>
    @endforeach

</table>
