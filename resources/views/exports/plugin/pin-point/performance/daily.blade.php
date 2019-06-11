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

            $targetCall = '=SUM(C3:C' . $totalUsersWithHeader . ')';
            $targetEffectiveCall = '=SUM(D3:D' . $totalUsersWithHeader . ')';
            $targetValue = '=SUM(E3:E' . $totalUsersWithHeader . ')';

            $actualCall = '=SUM(F3:F' . $totalUsersWithHeader . ')';
            $actualEffectiveCall = '=SUM(G3:G' . $totalUsersWithHeader . ')';
            $actualValue = '=SUM(H3:H' . $totalUsersWithHeader . ')';

            $actualCallPercentageCondition = 'IF(C' . $currentRow . ' > 0, F' . $currentRow . '/' . 'C' . $currentRow . ', 0)';
            $actualEffectiveCallPercentageCondition = 'IF(D' . $currentRow . ' > 0, G' . $currentRow . '/' . 'D' . $currentRow . ', 0)';

            $actualCallPercentage = '=IF(' . $actualCallPercentageCondition . ' < 1, ' . $actualCallPercentageCondition . ', 1)';
            $actualEffectiveCallPercentage = '=IF(' . $actualEffectiveCallPercentageCondition . ' < 1, ' . $actualEffectiveCallPercentageCondition . ', 1)';
            $actualValuePercentage = '=IF(E' . $currentRow . ' > 0, H' . $currentRow . '/' . 'E' . $currentRow . ', 0)';

            $averageActualCallPercentage = '=SUM(I3:I' . $totalUsersWithHeader . ') / ' . $totalUsers;
            $averageActualEffectiveCallPercentage = '=SUM(J3:J' . $totalUsersWithHeader . ') / ' . $totalUsers;
            $averageActualValuePercentage = '=SUM(K3:K' . $totalUsersWithHeader . ') / ' . $totalUsers;
        ?>
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->target_call }}</td>
            <td>{{ $user->target_effective_call  }}</td>
            <td>{{ $user->target_value  }}</td>
            <td>{{ $user->actual_call ?? 0 }}</td>
            <td>{{ $user->actual_effective_call ?? 0 }}</td>
            <td>{{ $user->actual_value ?? 0 }}</td>
            <td>{{ $actualCallPercentage }}</td>
            <td>{{ $actualEffectiveCallPercentage }}</td>
            <td>{{ $actualValuePercentage }}</td>

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
            <td>{{ $averageActualCallPercentage }}</td>
            <td>{{ $averageActualEffectiveCallPercentage }}</td>
            <td>{{ $averageActualValuePercentage }}</td>
            @for($i = 0; $i < count($totalItemSold); $i++)
                <td>{{ $totalItemSold[$i] }}</td>
            @endfor
        </tr>
    </tfoot>
    @endif
</table>
