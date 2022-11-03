@extends('emails.template')

@section('content')
<div class="title">Cancellation Approval Email</div>
<br>
<div class="body-text">
    Hello Mrs/Mr/Ms {{ $fullName }},
    <br>
    You Have an approval for Payment Cancellation. we would like to details as follows:
    <br>
    <div>
        <table style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
               border="0">
            <tbody>
                <tr>
                    <td style="width: 25%">Form Number</td>
                    <td>: {{ $payment->form->number ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="width: 25%">Form Date</td>
                    <td>: {{ date('d F Y', strtotime($payment->form->date)) ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="width: 25%">Form Reference</td>
                    <td>: {{ $payment->details()->first()->referenceable->form->number ?: '-' }}</td>
                </tr>
                @foreach ($payment->cashAdvances as $cashAdvance)
                <tr>
                    <td style="width: 25%">Cash Advance</td>
                    <td>: {{ $payment->form->number ?: '-' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td style="width: 25%">Amount Cash Advance</td>
                    <td>: {{ $payment->amount - $payment->details()->sum('amount') }}</td>
                </tr>
                <tr>
                    <td style="width: 25%">Cash Account</td>
                    <td>: {{ $payment->paymentAccount->label }}</td>
                </tr>
                <tr>
                    <td style="width: 25%">Person</td>
                    <td>: {{ $payment->paymentable_name }}</td>
                </tr>
                <tr>
                    <td style="width: 25%">Notes</td>
                    <td>: {{ $payment->form->notes ?: '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div>
        <table style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
               border="1">
            <thead>
                <tr>
                    <th style="padding: .5rem">No</th>
                    <th style="padding: .5rem">Account</th>
                    <th style="padding: .5rem">Notes</th>
                    <th style="padding: .5rem">Amount</th>
                    <th style="padding: .5rem">Allocation</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->details as $i => $detail)
                <tr>
                    <td style="padding: .5rem">
                        {{ ++$i }}
                    </td>
                    <td style="padding: .5rem">
                        {{ $detail->chartOfAccount->label }}
                    </td>
                    <td style="padding: .5rem">
                        {{ $detail->notes }}
                    </td>
                    <td style="padding: .5rem">
                        {{ $detail->amount }}
                    </td>
                    <td style="padding: .5rem">
                        {{ $detail->allocation->name }}
                    </td>
                </tr>
                @php ($i++)
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="text-align: center">
        <a href="{{ env('TENANT_DOMAIN') }}/finance/cash/out/{{ $payment->id }}"
           target="_blank"
           style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
            Check
        </a>
        <a href="{{ env('TENANT_DOMAIN') }}/approval?crud-type=delete&resource-type=Payment&action=approve&ids={{ $payment->id }}&approver_id={{ $approverId }}&token={{ $token }}"
           target="_blank"
           style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
            Approve
        </a>
        <a href="{{ env('TENANT_DOMAIN') }}/approval?crud-type=delete&resource-type=Payment&action=reject&ids={{ $payment->id }}&approver_id={{ $approverId }}&token={{ $token }}"
           target="_blank"
           style="background-color: rgb(255, 0, 0); border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
            Reject
        </a>
    </div>
    <br>
</div>
@stop