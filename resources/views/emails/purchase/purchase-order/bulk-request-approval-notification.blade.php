@extends('emails.template')

@section('content')
    <div style="align-content: center;position: center;text-align: center; font-family: Helvetica, Roboto, sans-serif;">
    <div class="title" style="text-align: center">Purchase Order</div>
    <hr>
    <div style="align-content: left;position: left;text-align: left">
        <h4>Hello Mrs/Mr/Ms {{ $purchaseOrders[0]->form->requestApprovalTo->full_name }}</h4>
        <p>You have an approval for Purchase Order. We would like to details as follows: </p>
        <table border="0">
            <tbody>
                <tr>
                    <td>Form number</td>
                    <td>: {{$purchaseOrders[0]->form->number}} - {{$purchaseOrders[count($purchaseOrders)-1]->form->number}}</td>
                </tr>
                <tr>
                    <td>Form date</td>
                    <td>: {{date('d F Y', strtotime($purchaseOrders[0]->form->date))}} - {{date('d F Y', strtotime($purchaseOrders[count($purchaseOrders)-1]->form->date))}}</td>
                </tr>
                <tr>
                    <td>Created at</td>
                    <td>: {{date('d F Y H:i', strtotime($purchaseOrders[0]->created_at))}} - {{date('d F Y H:i', strtotime($purchaseOrders[count($purchaseOrders)-1]->created_at))}}</td>
                </tr>
            </tbody>
        </table>
    </div>
        <table border="1" width="100%">
            <thead>
            <tr>
                <th style="padding: 5px">No</th>
                <th style="padding: 5px; min-width:120px;">Date Form</th>
                <th style="padding: 5px">Form Number</th>
                <th style="padding: 5px">Supplier</th>
                <th style="padding: 5px">Items</th>
                <th style="padding: 5px">Note</th>
                <th style="padding: 5px">Tax</th>
                <th style="padding: 5px">Discount</th>
                <th style="padding: 5px">Amount</th>
                <th style="padding: 5px">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($purchaseOrders as $index => $purchaseOrder)
                <tr>
                    <td style="padding: 5px">{{ $index + 1 }}</td>
                    <td style="padding: 5px; text-align: center">{{ date('d F Y', strtotime($purchaseOrder->form->date.' Asia/Jakarta')) }}</td>
                    <td style="padding: 5px; text-align: center">{{ $purchaseOrder->form->number }}</td>
                    <td style="padding: 5px; text-align: center">{{ $purchaseOrder->supplier->name }}</td>
                    <td style="padding: 5px; text-align: center">{{ $purchaseOrder->items->count() }}</td>
                    <td style="padding: 5px; text-align: center">{{ $purchaseOrder->form->notes }}</td>
                    <td style="padding: 5px; text-align: center">{{ $purchaseOrder->tax }}</td>
                    <td style="padding: 5px; text-align: center">{{ $purchaseOrder->discount ?: 0 }} ({{ ucwords($purchaseOrder->type_of_tax) }})</td>
                    <td style="padding: 5px; text-align: center">{{ $purchaseOrder->amount }}</td>
                    <td style="padding: 5px; text-align: center;">
                        <div style="display: flex;">
                            <a href="{{$tenantUrl}}/purchase/order/{{$purchaseOrder->id}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#666699; color:white; text-decoration: none;">Check</a>
                            <a href="{{$tenantUrl}}/approval?resource-type=PurchaseOrder&tenant={{$tenant}}&action=approve&id={{$purchaseOrder->id}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#1aa3ff; color:white; text-decoration: none;">Approve</a>
                            <a href="{{$tenantUrl}}/approval?resource-type=PurchaseOrder&tenant={{$tenant}}&action=reject&id={{$purchaseOrder->id}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#ff3333; color:white; text-decoration: none;">Reject</a>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div style="display: flex; margin-top:15px">
            <a href="{{$tenantUrl}}/approval-all?resource-type=PurchaseOrder&tenant={{$tenant}}&action=approve&ids={{json_encode($bulkId)}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#1aa3ff; color:white; text-decoration: none;">Approve All</a>
            <a href="{{$tenantUrl}}/approval-all?resource-type=PurchaseOrder&tenant={{$tenant}}&action=reject&ids={{json_encode($bulkId)}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#ff3333; color:white; text-decoration: none;">Reject All</a>
        </div>
    </div>
@stop
