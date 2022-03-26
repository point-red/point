<table>
    <thead>
        <tr>
            <th>Date Export</th>
            <th>{{ date('d F Y H:i') }}</th>
        </tr>
        <tr>
            <th></th>
        </tr>
        <tr>
            <th></th>
            <th>Item Code</th>
            <th>Item name</th>
            <th>Chart of Account</th>
            @for ($i = 1; $i <= $highestTotalItemUnit; $i++)
                <th>Unit Of Converter {{ $i }}</th>
                <th>Converter</th>
            @endfor
            <th>Expiry Date</th>
            <th>Production Number</th>
            <th>Default Purchase</th>
            <th>Default Sales</th>
            <th>Group</th>
        </tr>    
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td></td>
                <td>{{ $item->code }}</td>
                <td>{{ $item->name }}</td>
                @if ($account = $item->account)
                    <td>
                        {{ $account->number }} - {{ $account->alias }}
                    </td>
                @endif
                @foreach ($item->units as $unit)
                    <td>{{ $unit->name }}</td>
                    <td>{{ floatval($unit->converter) }}</td>
                @endforeach
                @php
                $remainingItemUnit = $highestTotalItemUnit - $item->units->count();
                @endphp
                @for ($i = 0; $i < $remainingItemUnit; $i++)
                    <td></td>
                    <td></td>
                @endfor    
                <td>{{ $item->require_expiry_date === 0 ? 'false' : 'true' }}</td>
                <td>{{ $item->require_production_number === 0 ? 'false' : 'true' }}</td>
                <td>
                    @if ($unitDefaultPurchase = $item->unitDefaultPurchase)
                        {{ $unitDefaultPurchase->name }}
                    @endif
                </td>
                <td>
                    @if ($unitDefaultSales = $item->unitDefaultSales)
                        {{ $unitDefaultSales->name }}
                    @endif
                </td>
                <td>{{ $item->groups->pluck('name', 'name')->join(', ') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
