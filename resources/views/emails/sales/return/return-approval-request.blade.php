@extends('emails.template')

@section('content')
    @php 
        $urlQueries = [
            'approver_id' => $approver->id,
            'token' => $approver->token
        ];
        $urlApprovalQueries = array_merge($urlQueries, ['resource-type' => 'salesReturns']); 
    @endphp
    <div class="title">{{ $salesReturns[0]->form->cancellation_status ? 'Cancellation' : '' }} Approval Email</div>
    <br>
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $approver->getFullNameAttribute() }},
        <br>
        You Have {{ $salesReturns[0]->form->cancellation_status ? 'a cancellation' : 'an' }} approval for Sales Return. we would like to details as follows:
        <br>
        <div>
          <table
              style="width: 100%; border-collapse: collapse;
                  margin-top: 2rem; margin-bottom: 2rem"
              border="0">
              <tbody>
                  <tr>
                      <td style="width: 25%">Form Number</td>
                      <td>: {{ $salesReturns[0]->form->number ?: '-' }}</td>
                  </tr>
                  <tr>
                      <td style="width: 25%">Form Date</td>
                      <td>: {{ date('d F Y', strtotime($salesReturns[0]->form->date)) ?: '-' }}</td>
                  </tr>
                  <tr>
                      <td style="width: 25%">Form Reference</td>
                      <td>: {{ $salesReturns[0]->salesInvoice->form->number ?: '-' }}</td>
                  </tr>
                  <tr>
                      <td style="width: 25%">Customer</td>
                      <td>: {{ $salesReturns[0]->customer->name ?: '-' }}</td>
                  </tr>
                  <tr>
                      <td style="width: 25%">Create at</td>
                      <td>: {{ date('d F Y', strtotime($salesReturns[0]->form->date)) ?: '-' }}</td>
                  </tr>
                  <tr>
                      <td style="width: 25%">Create by</td>
                      <td>: {{ $salesReturns[0]->form->createdBy->getFullNameAttribute() ?: '-' }}</td>
                  </tr>
                  <tr>
                      <td style="width: 25%">Notes</td>
                      <td>: {{ $salesReturns[0]->form->notes ?: '-' }}</td>
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
                        <th style="padding: .5rem">Item Name</th>
                        <th style="padding: .5rem">Quantity Sales</th>
                        <th style="padding: .5rem">Quantity Return</th>
                        <th style="padding: .5rem">Price</th>
                        <th style="padding: .5rem">Discount</th>
                        <th style="padding: .5rem">Total</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($salesReturns[0]->items as $item)
                    <tr>
                        <td style="padding: .5rem">
                            {{ $loop->iteration }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $item->item->name }}
                        </td>
                        <td style="padding: .5rem; text-align: right">
                            {{ $item->quantity_sales }}
                        </td>
                        <td style="padding: .5rem; text-align: right">
                            {{ $item->quantity }}
                        </td>
                        <td style="padding: .5rem; text-align: right">
                            {{ number_format($item->price) }}
                        </td>
                        <td style="padding: .5rem; text-align: right">
                            {{ number_format($item->discount_value) }}
                        </td>
                        <td style="padding: .5rem; text-align: right">
                            {{ number_format($item->quantity * ($item->price - $item->discount_value)) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                  <td colspan="6" style="padding: .5rem; text-align: right">
                    Sub Total
                  </td>
                  <td style="padding: .5rem; text-align: right">
                      {{ number_format($salesReturns[0]->amount - $salesReturns[0]->tax) }}
                  </td>
                </tr>
                <tr>
                  <td colspan="6" style="padding: .5rem; text-align: right">
                    Taxbase
                  </td>
                  <td style="padding: .5rem; text-align: right">
                      {{ number_format($salesReturns[0]->amount - $salesReturns[0]->tax) }}
                  </td>
                </tr>
                <tr>
                  <td colspan="6" style="padding: .5rem; text-align: right">
                    Tax
                  </td>
                  <td style="padding: .5rem; text-align: right">
                      {{ number_format($salesReturns[0]->tax) }}
                  </td>
                </tr>
                <tr>
                  <td colspan="6" style="padding: .5rem; text-align: right">
                    Taxbase
                  </td>
                  <td style="padding: .5rem; text-align: right">
                      {{ number_format($salesReturns[0]->amount) }}
                  </td>
                </tr>
                </tbody>
            </table>
        </div>
        @if (@$url)
        <div style="text-align: center">
            <a
                href="{{ $url ?: '-' }}sales/return/{{ $salesReturns[0]->id }}?approver_id={{ $approver->id }}&token={{ $approver->token }}"
                target="_blank"
                style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Check
            </a>
            <a
                href="{{ $url ?: '-' }}approval?crud-type={{ $salesReturns[0]->action }}&resource-type=SalesReturn&action=approve&ids={{ $salesReturns[0]->id }}&approver_id={{ $approver->id }}&token={{ $approver->token }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve
            </a>
            <a
                href="{{ $url ?: '-' }}approval?crud-type={{ $salesReturns[0]->action }}&resource-type=SalesReturn&action=reject&ids={{ $salesReturns[0]->id }}&approver_id={{ $approver->id }}&token={{ $approver->token }}"
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
