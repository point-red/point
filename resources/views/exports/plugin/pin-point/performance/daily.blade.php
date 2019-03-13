<table>
    <thead>
    <tr>
        <th></th>
        <th></th>
        <th colspan="3">TARGET</th>
        <th colspan="3">ACTUAL</th>
        <th colspan="3">ACTUAL (%)</th>
        <th colspan="{{ sizeof($items) }}"></th>
    </tr>
    <tr>
        <th>#</th>
        <th>NAME</th>
        <th>CALL</th>
        <th>EFFECTIVE CALL</th>
        <th>VALUE</th>
        <th>CALL</th>
        <th>EFFECTIVE CALL</th>
        <th>VALUE</th>
        <th>CALL (%)</th>
        <th>EFFECTIVE CALL (%)</th>
        <th>VALUE (%)</th>
        @foreach($items as $item)
            <th>{{ $item->name }}</th>
            <?php
                array_push($totalItemSold, 0);
            ?>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
        <?php
            $targetCall += $user->target_call;
            $targetEffectiveCall += $user->target_effective_call;
            $targetValue += $user->target_value;
            $actualCall += $user->actual_call;
            $actualEffectiveCall += $user->actual_effective_call;
            $actualValue += $user->actual_value;
            $actualCallPercentage += $user->target_call > 0 ? $user->actual_call / $user->target_call < 1 ? $user->actual_call / $user->target_call : 1 : 0;
            $actualEffectiveCallPercentage += $user->target_effective_call > 0 ? $user->actual_effective_call / $user->target_effective_call < 1 ? $user->actual_effective_call / $user->target_effective_call : 1 : 0;
            $actualValuePercentage += $user->target_value > 0 ? $user->actual_value / $user->target_value : 0;
        ?>
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->name  }}</td>
            <td>{{ $user->target_call  }}</td>
            <td>{{ $user->target_effective_call  }}</td>
            <td>{{ $user->target_value  }}</td>
            <td>{{ $user->actual_call ?? 0 }}</td>
            <td>{{ $user->actual_effective_call ?? 0 }}</td>
            <td>{{ $user->actual_value ?? 0 }}</td>
            <td>{{ $user->target_call > 0 ? $user->actual_call / $user->target_call < 1 ? $user->actual_call / $user->target_call : 1 : 0 }}</td>
            <td>{{ $user->target_effective_call > 0 ? $user->actual_effective_call / $user->target_effective_call < 1 ? $user->actual_effective_call / $user->target_effective_call : 1 : 0 }}</td>
            <td>{{ $user->target_value > 0 ? $user->actual_value / $user->target_value : 0 }}</td>

            @foreach ($items as $item)
                @foreach ($user->items as $itemSold)
                    @if ($item->id == $itemSold->item_id)
                        <td>{{ number_format($itemSold->quantity) }}</td>
                        <?php
                            $totalItemSold[$loop->parent->index] += $itemSold->quantity;
                        ?>
                        @break
                    @endif
                    @if ($loop->last)
                        <td>0</td>
                    @endif
                @endforeach
            @endforeach
        </tr>
    @endforeach
    </tbody>
    @if(count($users))
    <tfoot>
        <tr>
            <td></td> <!-- # -->
            <td></td> <!-- NAME -->
            <td>{{ $targetCall }}</td>
            <td>{{ $targetEffectiveCall }}</td>
            <td>{{ $targetValue }}</td>
            <td>{{ $actualCall }}</td>
            <td>{{ $actualEffectiveCall }}</td>
            <td>{{ $actualValue }}</td>
            <td>{{ $actualCallPercentage / count($users) }}</td>
            <td>{{ $actualEffectiveCallPercentage / count($users) }}</td>
            <td>{{ $actualValuePercentage / count($users) }}</td>
            @for($i = 0; $i < count($totalItemSold); $i++)
                <td>{{ $totalItemSold[$i] }}</td>
            @endfor
        </tr>
    </tfoot>
    @endif
</table>
