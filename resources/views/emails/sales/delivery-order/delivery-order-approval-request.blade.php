@extends('emails.template')

@section('content')
    @php 
        $urlQueries = [
            'approver_id' => $approver->id,
            'token' => $approver->token
        ];
        $urlApprovalQueries = array_merge($urlQueries, ['resource-type' => 'SalesDeliveryOrder']); 
    @endphp
    <div class="title">Request Approval All</div>
    <br>
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $approver->getFullNameAttribute() }},
        <br>
        You Have an approval for Sales Delivery Order . we would like to details as follows:
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
                        <th style="padding: .5rem">Warehouse</th>
                        <th>
                            <table style="width: 100%; table-layout: fixed;">
                                <tbody style="height: 100%;"><tr>
                                        <td style="border-right: 1px solid black; font-weight: bold;padding: .5rem;width: 70px;">Item</td>
                                        <td style="border-right: 1px solid black; font-weight: bold;padding: .5rem;width: 66.781px;">Quantitity Send</td>
                                        <td style="border-right: 1px solid black; font-weight: bold;padding: .5rem;width: 63.047px;">Quantity Delivered</td>
                                        <td style="font-weight: bold;padding: .5rem;width: 71.578px;">Quantity Remaining</td>
                                    </tr>
                                </tbody>
                            </table>
                        </th>
                        <th style="padding: .5rem">Note</th>
                        <th style="padding: .5rem">Created By</th>
                        <th style="padding: .5rem">Created At</th>
                        <th style="padding: .5rem"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($deliveryOrders as $deliveryOrder)
                    @php 
                        $deliveryOrderForm = $deliveryOrder->form; 

                        $urlApprovalQueries['ids'] = $deliveryOrder->id;
                        $urlApprovalQueries['crud-type'] = $deliveryOrder->action;
                    @endphp
                    <tr>
                        <td style="padding: .5rem">
                            {{ $loop->iteration }}
                        </td>
                        <td style="padding: .5rem">
                            {{ date('d M Y', strtotime($deliveryOrderForm->date)) }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $deliveryOrderForm->number }}
                            {{ ' ' }}
                            {{ 
                                !is_null($deliveryOrderForm->close_status) 
                                && in_array($deliveryOrderForm->close_status, [0, 1]) 
                                    ? ' - Closed' 
                                    : '' 
                            }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $deliveryOrder->salesOrder->form->number }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $deliveryOrder->customer->name }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $deliveryOrder->warehouse->name }}
                        </td>
                        <td style="vertical-align: top; padding: 0px">
                            <table style="width: 100%; table-layout: fixed;">
                                <tbody style="height: 100%;">
                                    @foreach($deliveryOrder->items as $item)
                                    @php $borderBottom = !$loop->last ? 'border-bottom: 1px solid black' : ''; @endphp
                                    <tr>
                                        <td style="border-right: 1px solid black; padding: .5rem;width: 70px; {{ $borderBottom }}">
                                            {{ $item->item->name }}
                                        </td>
                                        <td style="border-right: 1px solid black; padding: .5rem;width: 66.781px; {{ $borderBottom }}">
                                            {{ $item->quantity_requested }}
                                        </td>
                                        <td style="border-right: 1px solid black; padding: .5rem;width: 63.047px; {{ $borderBottom }}">
                                            {{ $item->quantity_delivered }}
                                        </td>
                                        <td style="padding: .5rem;width: 71.578px; {{ $borderBottom }}">
                                            {{ $item->quantity_remaining }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                        <td style="padding: .5rem">
                            {{ $item->note }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $deliveryOrderForm->createdBy->getFullNameAttribute() }}
                        </td>
                        <td style="padding: .5rem">
                            {{ date('d M Y, H:i', strtotime($deliveryOrderForm->created_at)) }}
                        </td>
                        <td style="padding: .5rem">
                            <div style="display: flex; justify-content: space-between; text-align: center">
                                <a
                                    href="{{ env('TENANT_DOMAIN') . 'sales/delivery-order/'. $deliveryOrder->id }}"
                                    target="_blank"
                                    style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Check
                                </a>
                                <a
                                    href="{{ env('TENANT_DOMAIN') . 'approval?action=approve&' . http_build_query($urlApprovalQueries) }}"
                                    target="_blank"
                                    style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                    Approve
                                </a>
                                <a
                                    href="{{ env('TENANT_DOMAIN') . 'approval?action=reject&' . http_build_query($urlApprovalQueries) }}"
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
        <div style="text-align: center">
            @php 
                unset($urlApprovalQueries['crud-type']);
                $urlApprovalQueries['ids'] = implode(",", Illuminate\Support\Arr::pluck($deliveryOrders, 'id')); 
            @endphp
            <a
                href="{{ env('TENANT_DOMAIN') .'approval-all?action=approve&' . http_build_query($urlApprovalQueries) }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve All
            </a>
            <a
                href="{{ env('TENANT_DOMAIN') .'approval-all?action=reject&' . http_build_query($urlApprovalQueries) }}"
                target="_blank"
                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Reject All
            </a>
        </div>
        <br>
    </div>
@stop
