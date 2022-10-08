@extends('emails.template')

@section('content')
    @php 
        $urlQueries = [
            'approver_id' => $approver->id,
            'token' => $approver->token,
            'ids' => $inventoryUsage->id,
            'crud-type' => 'create',
            'resource-type' => 'InventoryUsage'
        ];
    @endphp
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $approver->getFullNameAttribute() }},
        <br>
        You Have an approval request for Inventory Usage . we would like to details as follows:
        <br>
        <div>
            <table
                style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
                border="0">
                <tbody>
                    <tr>
                        <td style="width: 25%">Form Number</td>
                        <td>: {{ $form['number'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Form Date</td>
                        <td>: {{ date('d M Y', strtotime($form['date'])) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Employee</td>
                        <td>: {{ optional($inventoryUsage->employee)->name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Create at</td>
                        <td>: {{ date('d M Y H:i', strtotime($form['created_at'])) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Create By</td>
                        <td>: {{ optional($form->createdBy)->name }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Notes</td>
                        <td>: {{ $form['notes'] ?: '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div>
            <table
                style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
                border="1">
                <thead>
                    <tr>
                        <th style="padding: .5rem">No</th>
                        <th style="padding: .5rem">Item</th>
                        <th style="padding: .5rem">Chart of Account</th>
                        <th style="padding: .5rem">Quantity Usage</th>
                        <th style="padding: .5rem">Notes</th>
                        <th style="padding: .5rem">Allocation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventoryUsage->items as $key => $inventoryUsageItem)
                        <tr>
                            <td style="padding: .5rem">
                                {{ $loop->iteration }}
                            </td>
                            <td style="padding: .5rem">
                                {{ $inventoryUsageItem->item->name }}
                            </td>
                            <td style="padding: .5rem">
                                {{ \Illuminate\Support\Str::title($inventoryUsageItem->account->alias) }}
                            </td>
                            <td style="padding: .5rem">
                                {{ $inventoryUsageItem->quantity }} {{ $inventoryUsageItem->unit }}
                            </td>
                            <td style="padding: .5rem">
                                {{ $inventoryUsageItem->notes ?: '-' }}
                            </td>
                            <td style="padding: .5rem">
                                {{ optional($inventoryUsageItem->allocation)->name }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="text-align: center">
            <a
                href="{{ env('TENANT_DOMAIN') . 'approval?action=approve&' . http_build_query($urlQueries) }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve
            </a>
            <a
                href="{{ env('TENANT_DOMAIN') . 'approval?action=reject&' . http_build_query($urlQueries) }}"
                target="_blank"
                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Reject
            </a>
        </div>
        <br>
    </div>
@stop
