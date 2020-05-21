@extends('emails.template')

@section('content')
    <div style="align-content: center;position: center;text-align: center">
        <div class="title" style="text-align: center">Employee Contract Expired</div>
        <hr>
        <table border="1" width="100%">
            <thead>
            <tr>
                <th style="padding: 5px">Employee</th>
                <th style="padding: 5px">Contract Begin</th>
                <th style="padding: 5px">Contract End</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employeeContractExpired as $employeeContract)
                <tr>
                    <td style="padding: 5px">{{ $employeeContract->employee->name }}</td>
                    <td style="padding: 5px; text-align: center">{{ date('d F Y', strtotime($employeeContract->contract_begin)) }}</td>
                    <td style="padding: 5px; text-align: center">{{ date('d F Y', strtotime($employeeContract->contract_end)) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        @if($employeeContractExpiredSoon->count() > 0)

        <hr>
        <div class="title" style="text-align: center">Employee Contract Expired Soon</div>
        <hr>

        <table border="1" width="100%">
            <thead>
            <tr>
                <th style="padding: 5px">Employee</th>
                <th style="padding: 5px">Contract Begin</th>
                <th style="padding: 5px">Contract End</th>
            </tr>
            </thead>
            <tbody>
            @foreach($employeeContractExpiredSoon as $employeeContract)
                <tr>
                    <td style="padding: 5px">{{ $employeeContract->employee->name }}</td>
                    <td style="padding: 5px; text-align: center">{{ date('d F Y', strtotime($employeeContract->contract_begin)) }}</td>
                    <td style="padding: 5px; text-align: center">{{ date('d F Y', strtotime($employeeContract->contract_end)) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
@stop
