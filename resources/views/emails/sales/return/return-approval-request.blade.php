@extends('emails.template')

@section('content')
    @php 
        $urlQueries = [
            'approver_id' => $approver->id,
            'token' => $approver->token
        ];
        $urlApprovalQueries = array_merge($urlQueries, ['resource-type' => 'SalesReturn']); 
    @endphp
    <div class="title">Request Approval All</div>
    <br>
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $approver->getFullNameAttribute() }},
        <br>
        You Have an approval for Sales Return. we would like to details as follows:
        <br>
        <div>
            <table
                style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
                border="0">
                <tbody>
                    <tr>
                        <td style="width: 25%">Form Number</td>
                        <td>: {{ optional($form)->number }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Form Date</td>
                        <td>: {{ optional($form)->date }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Create at</td>
                        <td>: {{ optional($form)->created }}</td>
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
                        <th style="padding: .5rem">Form Reference</th>
                        <th style="padding: .5rem">Customer</th>
                        <th style="padding: .5rem">Item</th>
                        <th style="padding: .5rem">Quantity Return</th>
                        <th style="padding: .5rem">Note</th>
                        <th style="padding: .5rem">Created By</th>
                        <th style="padding: .5rem">Created At</th>
                        <th style="padding: .5rem"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($salesReturns as $salesReturn)
                    @php 
                        $salesReturnForm = $salesReturn->form; 

                        $urlApprovalQueries['ids'] = $salesReturn->id;
                        $urlApprovalQueries['crud-type'] = $salesReturn->action;
                    @endphp
                    <tr>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ $loop->iteration }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ date('d M Y', strtotime($salesReturnForm->date)) }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ $salesReturnForm->number }}
                            {{ ' ' }}
                            {{ 
                                !is_null($salesReturnForm->close_status) 
                                && in_array($salesReturnForm->close_status, [0, 1]) 
                                    ? ' - Closed' 
                                    : '' 
                            }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ $salesReturn->salesInvoice->form->number }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ $salesReturn->customer->name }}
                        </td>
                        @foreach($salesReturn->items as $item)
                        <td style="padding: .5rem;">
                            {{ $item->item->name }}
                        </td>
                        <td style="padding: .5rem;">
                            {{ $item->quantity }}
                        </td>
                        @break
                        @endforeach
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ $salesReturnForm->notes }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ $salesReturnForm->createdBy->getFullNameAttribute() }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            {{ date('d M Y, H:i', strtotime($salesReturnForm->created_at)) }}
                        </td>
                        <td style="padding: .5rem" rowspan="{{ count($salesReturn->items) }}">
                            <div style="display: flex;">
                                <a
                                    href="{{ $url ?: env('TENANT_DOMAIN') }}sales/return/{{ $salesReturn->id }}"
                                    target="_blank"
                                    style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Check
                                </a>
                                <a
                                    href="{{ $url ?: env('TENANT_DOMAIN') }}approval?action=approve&{{ http_build_query($urlApprovalQueries) }}"
                                    target="_blank"
                                    style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Approve
                                </a>
                                <a
                                    href="{{ $url ?: env('TENANT_DOMAIN') }}approval?action=reject&{{ http_build_query($urlApprovalQueries) }}"
                                    target="_blank"
                                    style="background-color: rgb(255, 0, 0); border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Reject
                                </a>
                            </div>
                        </td>
                    </tr>
                    @php 
                        ($first = true); 
                    @endphp
                    @foreach($salesReturn->items as $item)
                    @if($first)
                        @php 
                            ($first = false);
                        @endphp
                        @continue
                    @endif
                    <tr>
                        <td style="padding: .5rem;">
                            {{ $item->item->name }}
                        </td>
                        <td style="padding: .5rem;">
                            {{ $item->quantity }}
                        </td>
                    </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
        <div style="text-align: center">
            @php 
                unset($urlApprovalQueries['crud-type']);
                $urlApprovalQueries['ids'] = implode(",", Illuminate\Support\Arr::pluck($salesReturns, 'id')); 
            @endphp
            <a
                href="{{ $url ?: env('TENANT_DOMAIN') }}approval-all?action=approve&{{ http_build_query($urlApprovalQueries) }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve All
            </a>
            <a
                href="{{ $url ?: env('TENANT_DOMAIN') }}approval-all?action=reject&{{ http_build_query($urlApprovalQueries) }}"
                target="_blank"
                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Reject All
            </a>
        </div>
        <br>
    </div>
@stop
