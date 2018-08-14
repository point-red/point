@extends('emails.template')

@section('content')
    <div class="title">Password Reset</div>
    <br>
    <div class="body-text">
        Hello,
        <br>
        You have just initiated a request to reset the password in cloud.point.red
        To set a new password, please click the button below:
        <br>
        <a href="{{ $url }}" style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">Reset Password</a>
        <br>
        If you can't confirm by clicking the button above, please copy the address below to the browser address bar to confirm.
        <br> <a href="{{ $url }}">{{ $url }}</a>
    </div>
@stop
