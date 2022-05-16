@extends('emails.template')

@section('content')
    <div class="title">Approval Email</div>
    <br>
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $fullName }},
        <br>
        You Have an approval for Transfer Item . we would like to details as follows:
        <br>
        <div>
            <table
                style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
                border="0">
                <tbody>
                    <tr>
                        <td style="width: 25%">Form Number</td>
                        <td>: {{ $receiveItem->form->number }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Form Date</td>
                        <td>: {{ date('d F Y', strtotime($receiveItem->form->date)) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Form Reference</td>
                        <td>: {{ $receiveItem->transfer_item->form->number }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">From Warehouse</td>
                        <td>: {{ $receiveItem->from_warehouse->name }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Update At</td>
                        <td>: {{ date('d F Y H:i:s', strtotime($receiveItem->form->updated_at)) }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Update By</td>
                        <td>: {{ $updated_by }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Notes</td>
                        <td>: {{ $receiveItem->form->notes }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Action</td>
                        <td>: {{ ucwords($crudType) }}</td>
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
                        <th style="padding: .5rem">Production Number</th>
                        <th style="padding: .5rem">Expiry Date</th>
                        <th style="padding: .5rem">Quantity Receive</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($receiveItem->items as $key=>$item)
                    <tr>
                        <td style="padding: .5rem">
                            {{ $key+1 }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $item->item_name ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $item->production_number ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ date('d F Y', strtotime($item->expiry_date)) ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $item->quantity ?: '-' }} {{ $item->unit ?: '-' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if (@$url)
        <div style="text-align: center">
            <a
                href="{{ $url }}approval?resource-type=TransferReceive&action=approve&crud-type={{ $crudType }}&ids={{ $receiveItem->id }}&approver_id={{ $approverId }}&token={{ $token }}&form_send_done={{ $formSendDone }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve
            </a>
            <a
                href="{{ $url }}approval?resource-type=TransferReceive&action=reject&crud-type={{ $crudType }}&ids={{ $receiveItem->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Reject
            </a>
        </div>
        @else
        <p>
            Open your dashboard to check.
        </p>
        @endif
        <br>
    </div>
@stop
