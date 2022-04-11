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
                        <td>: {{ $form['number'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Form Date</td>
                        <td>: {{ $form['date'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Create at</td>
                        <td>: {{ $form['created'] ?: '-' }}</td>
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
                        <th style="padding: .5rem">Form Date</th>
                        <th style="padding: .5rem">Form Number</th>
                        <th style="padding: .5rem">Warehouse Send</th>
                        <th style="padding: .5rem">Warehouse Receive</th>
                        <th style="padding: .5rem">Notes</th>
                        <th style="padding: .5rem">Created By</th>
                        <th style="padding: .5rem">Activity</th>
                        <th style="padding: .5rem">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($transferItems as $transferItem)
                    <tr>
                        <td style="padding: .5rem">
                            {{ $transferItem->no }}
                        </td>
                        <td style="padding: .5rem">
                            {{ date('d F Y', strtotime($transferItem->form->date)) ?: '-' }}
                            <!-- {{ $transferItem->form->date ?: '-' }} -->
                        </td>
                        <td style="padding: .5rem">
                            {{ $transferItem->form->number ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $transferItem->warehouse->name ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $transferItem->to_warehouse->name ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $transferItem->form->notes ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $transferItem->created_by ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $transferItem->action ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            <div style="text-align: center">
                                <a
                                    href="{{ $url ?: '-' }}inventory/transfer/send/{{ $transferItem->id }}?approver_id={{ $approverId }}&token={{ $token }}"
                                    target="_blank"
                                    style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Check
                                </a>
                                <a
                                    href="{{ $url ?: '-' }}approval?crud-type={{ $transferItem->action }}&resource-type=TransferSend&action=approve&ids={{ $transferItem->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                                    target="_blank"
                                    style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Approve
                                </a>
                                <a
                                    href="{{ $url ?: '-' }}approval?crud-type={{ $transferItem->action }}&resource-type=TransferSend&action=reject&ids={{ $transferItem->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                                    target="_blank"
                                    style="background-color: rgb(255, 0, 0); border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Reject
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if (@$url)
        <div style="text-align: center">
            <a
                href="{{ $url }}approval-all?resource-type=TransferSend&action=approve&ids={{ $ids }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve All
            </a>
            <a
                href="{{ $url }}approval-all?resource-type=TransferSend&action=reject&ids={{ $ids }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Reject All
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
