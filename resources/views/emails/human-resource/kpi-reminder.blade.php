@extends('emails.template')

@section('content')
<div style="align-content: center;position: center;text-align: center">
  <div class="title" style="text-align: center">KPI Reminder</div>
  <hr>
  <div style="align-content: left;position: left;text-align: left">
    <h4>Halo, {{ $name }}</h4>
    <h5>Mengingatkan untuk segera melakukan penilaian KPI berikut: </h5>
  </div>
  <table width="50%">
    <tr style="align-content: left;position: left;text-align: left">
      <th style="padding: 5px">Employee Name</th>
      <td style="padding: 5px">: {{ $employeeName }}</td>
    </tr>
    <tr style="align-content: left;position: left;text-align: left">
      <th style="padding: 5px">Periode</th>
      <td style="padding: 5px">: {{ $periode }}</td>
    </tr>
  </table>
</div>
@stop