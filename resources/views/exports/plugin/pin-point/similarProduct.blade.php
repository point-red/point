<table>
    <thead>
    <tr>
        <th></th>
        <th colspan="{{ sizeof($products) }}">Similar Product</th>
    </tr>
    <tr>
        <th>Week</th>
        @foreach($products as $product)
            <th>{{ $product }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($similarProducts as $similarProduct)
        <tr>
            <td>{{ $similarProduct->week }}</td>
            @foreach ($similarProduct->products as $item)
                <td>{{ !empty($item->total) ? $item->total : 0 }}</td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>
