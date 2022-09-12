@extends('emails.template')

@section('content')
    <div class="title">Approval Email</div>
    <br>
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $fullName }},
        <br>
        You Have an approval for Payment Collection . we would like to details as follows:
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
                        <th style="padding: .5rem">Customer</th>
                        <th style="padding: .5rem">Form Date</th>
                        <th style="padding: .5rem">Form Number</th>
                        <th style="padding: .5rem">Reference</th>
                        <th style="padding: .5rem">Total Collection</th>
                        <th style="padding: .5rem">Total Payment</th>
                        <th style="padding: .5rem">Notes</th>
                        <th style="padding: .5rem">Created By</th>
                        <th style="padding: .5rem">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($paymentCollections as $paymentCollection)
                    <tr>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ $paymentCollection->no }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ $paymentCollection->customer->name ?: '-' }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ date('d F Y', strtotime($paymentCollection->form->date)) ?: '-' }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ $paymentCollection->form->number ?: '-' }}
                        </td>
                        @foreach($paymentCollection->details as $detail)
                            <td style="padding: .5rem">
                                {{ $detail->referenceable_form_number ?: $detail->chartOfAccount->label }}
                            </td>
                            <td style="padding: .5rem">
                                @php ($minus = '')
                                @if($detail->referenceable_form_number)
                                    @if($detail->referenceable_type === 'SalesDownPayment' || $detail->referenceable_type === 'SalesReturn')
                                        @php ($minus = '-')
                                    @endif
                                @else
                                    @if($detail->chartOfAccount->type->is_debit === 1)
                                        @php ($minus = '-')
                                    @endif
                                @endif
                                {{ $minus.''.$detail->amount ?: '0' }}
                            </td>
                            @break
                        @endforeach
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ $paymentCollection->amount ?: '0' }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ $paymentCollection->form->notes ?: '-' }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ $paymentCollection->created_by ?: '-' }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            {{ $paymentCollection->action ?: '-' }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($paymentCollection->details) }}">
                            <div style="text-align: center">
                                <a
                                    href="{{ $url ?: '-' }}sales/payment-collection/{{ $paymentCollection->id }}?approver_id={{ $approverId }}&token={{ $token }}"
                                    target="_blank"
                                    style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Check
                                </a>
                                <a
                                    href="{{ $url ?: '-' }}approval?crud-type={{ $paymentCollection->action }}&resource-type=PaymentCollection&action=approve&ids={{ $paymentCollection->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                                    target="_blank"
                                    style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Approve
                                </a>
                                <a
                                    href="{{ $url ?: '-' }}approval?crud-type={{ $paymentCollection->action }}&resource-type=PaymentCollection&action=reject&ids={{ $paymentCollection->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                                    target="_blank"
                                    style="background-color: rgb(255, 0, 0); border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Reject
                                </a>
                            </div>
                        </td>
                    </tr>
                    @php ($first = true)
                    @foreach($paymentCollection->details as $detail)
                        @if($first)
                            @php ($first = false)
                            @continue
                        @endif
                        <tr>
                            <td style="padding: .5rem">
                                {{ $detail->referenceable_form_number ?: $detail->chartOfAccount->label }}
                            </td>
                            <td style="padding: .5rem">
                                {{ $detail->amount ?: '0' }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
        @if (@$url)
        <div style="text-align: center">
            <a
                href="{{ $url }}approval-all?resource-type=PaymentCollection&action=approve&ids={{ $ids }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve All
            </a>
            <a
                href="{{ $url }}approval-all?resource-type=PaymentCollection&action=reject&ids={{ $ids }}&approver_id={{ $approverId }}&token={{ $token }}"
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
