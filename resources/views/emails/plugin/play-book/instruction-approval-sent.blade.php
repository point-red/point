@extends('emails.template')

@section('content')
    <div class="title">New Approval Request</div>
    <br>
    <div class="body-text">
        Hello {{ $name }},
        <br>
        There is a new {{ $type }} approval request just sent to you.
        <br>
        @if (@$url)
        <a
            href="{{ $url }}"
            style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
            Open
        </a>
        @else
        <p>
            Open your dashboard to check.
        </p>
        @endif
        <br>
    </div>
@stop
