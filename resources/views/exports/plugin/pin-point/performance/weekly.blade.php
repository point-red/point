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
    <?php
        $currentRow = 2; // Header

        $totalUsers = count($users);
        $totalUsersWithHeader = 2 + $totalUsers;

        $totalRows = 2; // Header
        $totalRows += $totalUsers;
        $totalRows += 3; // Footer
    ?>
    @foreach($users as $user)
        <?php
            $currentRow++;

            $weeklyTargetCall = '=PRODUCT(' . $user->target_call . ',D' . $totalRows . ')';
            $weeklyTargetEffectiveCall = '=PRODUCT(' . $user->target_effective_call . ',D' . $totalRows . ')';
            $weeklyTargetValue = '=PRODUCT(' . $user->target_value . ',D' . $totalRows . ')';

            $weeklyTargetCallPercentageCondition = 'IF(C' . $currentRow . ' > 0, F' . $currentRow . '/' . 'C' . $currentRow . ', 0)';
            $weeklyTargetEffectiveCallPercentageCondition = 'IF(D' . $currentRow . ' > 0, G' . $currentRow . '/' . 'D' . $currentRow . ', 0)';

            $weeklyTargetCallPercentage = '=IF(' . $weeklyTargetCallPercentageCondition . ' < 1, ' . $weeklyTargetCallPercentageCondition . ', 1)';
            $weeklyTargetEffectiveCallPercentage = '=IF(' . $weeklyTargetEffectiveCallPercentageCondition . ' < 1, ' . $weeklyTargetEffectiveCallPercentageCondition . ', 1)';
            $weeklyTargetValuePercentage = '=IF(E' . $currentRow . ' > 0, H' . $currentRow . '/' . 'E' . $currentRow . ', 0)';

            $targetCall = '=SUM(C3:C' . $totalUsersWithHeader . ')';
            $targetEffectiveCall = '=SUM(D3:D' . $totalUsersWithHeader . ')';
            $targetValue = '=SUM(E3:E' . $totalUsersWithHeader . ')';

            $actualCall = '=SUM(F3:F' . $totalUsersWithHeader . ')';
            $actualEffectiveCall = '=SUM(G3:G' . $totalUsersWithHeader . ')';
            $actualValue = '=SUM(H3:H' . $totalUsersWithHeader . ')';

            $averageActualCallPercentage = '=SUM(I3:I' . $totalUsersWithHeader . ') / ' . $totalUsers;
            $averageActualEffectiveCallPercentage = '=SUM(J3:J' . $totalUsersWithHeader . ') / ' . $totalUsers;
            $averageActualValuePercentage = '=SUM(K3:K' . $totalUsersWithHeader . ') / ' . $totalUsers;
        ?>
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $weeklyTargetCall }}</td>
            <td>{{ $weeklyTargetEffectiveCall }}</td>
            <td>{{ $weeklyTargetValue }}</td>
            <td>{{ $user->actual_call ?? 0 }}</td>
            <td>{{ $user->actual_effective_call ?? 0 }}</td>
            <td>{{ $user->actual_value ?? 0 }}</td>
            <td>{{ $weeklyTargetCallPercentage }}</td>
            <td>{{ $weeklyTargetEffectiveCallPercentage }}</td>
            <td>{{ $weeklyTargetValuePercentage }}</td>

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
            <td colspan="2">Total</td>
            <td>{{ $targetCall }}</td>
            <td>{{ $targetEffectiveCall }}</td>
            <td>{{ $targetValue }}</td>
            <td>{{ $actualCall }}</td>
            <td>{{ $actualEffectiveCall }}</td>
            <td>{{ $actualValue }}</td>
            <td>{{ $averageActualCallPercentage }}</td>
            <td>{{ $averageActualEffectiveCallPercentage }}</td>
            <td>{{ $averageActualValuePercentage }}</td>
            @for($i = 0; $i < count($totalItemSold); $i++)
                <td>{{ $totalItemSold[$i] }}</td>
            @endfor
        </tr>
        <tr/>
        <tr>
            <td colspan="3" style="text-align:right"><b>Total Days</b></td>
            <td>{{ $totalDay }}</td>
        </tr>
    </tfoot>
    @endif
</table>
