@extends('emails.template')

@section('content')
    <div style="align-content: center;position: center;text-align: center; font-family: Helvetica, Roboto, sans-serif;">
    <div class="title" style="text-align: center">Cash Advance</div>
    <hr>
    <div style="align-content: left;position: left;text-align: left">
        <h4>Hello Mrs/Mr/Ms {{ $cashAdvances[0]->form->requestApprovalTo->full_name }}</h4>
        <p>You have an approval for Cash Advance All. We would like to details as follows: </p>
        <table border="0">
            <tbody>
                <tr>
                    <td>Form number</td>
                    <td>: {{$cashAdvances[0]->form->number}} - {{$cashAdvances[count($cashAdvances)-1]->form->number}}</td>
                </tr>
                <tr>
                    <td>Form date</td>
                    <td>: {{date('d F Y', strtotime($cashAdvances[0]->form->date))}} - {{date('d F Y', strtotime($cashAdvances[count($cashAdvances)-1]->form->date))}}</td>
                </tr>
                <tr>
                    <td>Created at</td>
                    <td>: {{date('d F Y H:i', strtotime($cashAdvances[0]->created_at))}} - {{date('d F Y H:i', strtotime($cashAdvances[count($cashAdvances)-1]->created_at))}}</td>
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
                <th style="padding: 5px">Account</th>
                <th style="padding: 5px">Notes</th>
                <th style="padding: 5px">Amount</th>
                <th style="padding: 5px">Employee</th>
                <th style="padding: 5px">Created By</th>
                <th style="padding: 5px; min-width:120px;">Created At</th>
                <th style="padding: 5px">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cashAdvances as $index => $cashAdvance)
            @foreach($cashAdvance->details as $detail)
                <tr>
                    <td style="padding: 5px">{{ $index + 1 }}</td>
                    <td style="padding: 5px; text-align: center">{{ date('d F Y', strtotime($cashAdvance->form->date.' Asia/Jakarta')) }}</td>
                    <td style="padding: 5px; text-align: center">{{ $cashAdvance->form->number }}</td>
                    <td style="padding: 5px; text-align: center">{{ $detail->account->alias }}</td>
                    <td style="padding: 5px; text-align: center">{{ $detail->notes }}</td>
                    <td style="padding: 5px; text-align: center">{{ $cashAdvance->amount }}</td>
                    <td style="padding: 5px; text-align: center">{{ $cashAdvance->employee->name }}</td>
                    <td style="padding: 5px; text-align: center">{{ $cashAdvance->form->createdBy->full_name }}</td>
                    <td style="padding: 5px; text-align: center">{{ date('d F Y', strtotime($cashAdvance->created_at.' Asia/Jakarta')) }}</td>
                    <td style="padding: 5px; text-align: center;">
                        <div style="display: flex;">
                            <a href="{{$tenantUrl}}/finance/cash-advance/{{$cashAdvance->id}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#666699; color:white; text-decoration: none;">Check</a>
                            <a href="{{$tenantUrl}}/approval?resource-type=CashAdvance&tenant={{$tenant}}&action=approve&id={{$cashAdvance->id}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#1aa3ff; color:white; text-decoration: none;">Approve</a>
                            <a href="{{$tenantUrl}}/approval?resource-type=CashAdvance&tenant={{$tenant}}&action=reject&id={{$cashAdvance->id}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#ff3333; color:white; text-decoration: none;">Reject</a>
                        </div>
                    </td>
                </tr>
            @endforeach
            @endforeach
            </tbody>
        </table>
        <div style="display: flex; margin-top:15px">
            <a href="{{$tenantUrl}}/approval-all?resource-type=CashAdvance&tenant={{$tenant}}&action=approve&ids={{json_encode($bulkId)}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#1aa3ff; color:white; text-decoration: none;">Approve All</a>
            <a href="{{$tenantUrl}}/approval-all?resource-type=CashAdvance&tenant={{$tenant}}&action=reject&ids={{json_encode($bulkId)}}&project-name={{$projectName}}&token={{$token}}" style="padding: 5px 15px 5px 15px; margin: 2px; min-width: 30px; border-radius: 5px; background-color:#ff3333; color:white; text-decoration: none;">Reject All</a>
        </div>
    </div>
@stop
