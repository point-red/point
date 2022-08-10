@extends('emails.template')

@section('content')
    <div class="title">Cancellation Approval Email</div>
    <br>
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $fullName }},
        <br>
        You Have an approval for Payment Collection Cancellation. we would like to details as follows:
        <br>
        <div>
            <table
                style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
                border="0">
                <tbody>
                    <tr>
                        <td style="width: 25%">Form Number</td>
                        <td>: {{ $paymentCollection->form->number ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Form Date</td>
                        <td>: {{ date('d F Y', strtotime($paymentCollection->form->date)) ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Customer</td>
                        <td>: {{ $paymentCollection->customer->name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Cancel at</td>
                        <td>: {{ date('d F Y', strtotime($paymentCollection->form->request_cancellation_at)) ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Cancel by</td>
                        <td>: {{ $form['cancelBy'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Notes</td>
                        <td>: {{ $paymentCollection->form->notes ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Reason</td>
                        <td>: {{ $paymentCollection->form->request_cancellation_reason ?: '-' }}</td>
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
                        <th style="padding: .5rem">Collection</th>
                        <th style="padding: .5rem">Notes</th>
                        <th style="padding: .5rem">Available</th>
                        <th style="padding: .5rem">Jumlah yang harus ditagih</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($paymentCollection->details as $i => $detail)
                    <tr>
                        <td style="padding: .5rem">
                            {{ ++$i }}
                        </td>
                        <td style="padding: .5rem">
                          {{ $detail->referenceable_form_number ?: $detail->chartOfAccount->label }}
                        </td>
                        <td style="padding: .5rem">
                          {{ $detail->referenceable_form_notes }}
                        </td>
                        <td style="padding: .5rem">
                          {{ $detail->available? : $detail->amount }}
                        </td>
                        <td style="padding: .5rem">
                          {{ $detail->amount }}
                        </td>                        
                    </tr>
                    @php ($i++)
                  @endforeach
                  <tr>
                    <td style="padding: .5rem">
                      &nbsp;
                    </td>
                    <td style="padding: .5rem" colspan="3">
                      Total Sales Invoice
                    </td>
                    <td style="padding: .5rem">
                      {{ $form['total_invoice'] ?: '0' }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: .5rem">
                      &nbsp;
                    </td>
                    <td style="padding: .5rem" colspan="3">
                      Total Down Payment
                    </td>
                    <td style="padding: .5rem">
                      {{ $form['total_down_payment'] ?: '0' }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: .5rem">
                      &nbsp;
                    </td>
                    <td style="padding: .5rem" colspan="3">
                      Total Sales Return
                    </td>
                    <td style="padding: .5rem">
                      {{ $form['total_return'] ?: '0' }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: .5rem">
                      &nbsp;
                    </td>
                    <td style="padding: .5rem" colspan="3">
                      Total Others
                    </td>
                    <td style="padding: .5rem">
                      {{ $form['total_other'] ?: '0' }}
                    </td>
                  </tr>
                  <tr>
                    <td style="padding: .5rem">
                      &nbsp;
                    </td>
                    <td style="padding: .5rem" colspan="3">
                      Total Amount
                    </td>
                    <td style="padding: .5rem">
                      {{ $paymentCollection->amount ?: '0' }}
                    </td>
                  </tr>
                </tbody>
            </table>
        </div>
        @if (@$url)
        <div style="text-align: center">
            <a
                href="{{ $url ?: '-' }}sales/payment-collection/{{ $paymentCollection->id }}?approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Check
            </a>
            <a
                href="{{ $url ?: '-' }}approval?crud-type={{ $form['action'] }}&resource-type=PaymentCollection&action=approve&ids={{ $paymentCollection->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve
            </a>
            <a
                href="{{ $url ?: '-' }}approval?crud-type={{ $form['action'] }}&resource-type=PaymentCollection&action=reject&ids={{ $paymentCollection->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: rgb(255, 0, 0); border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
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
