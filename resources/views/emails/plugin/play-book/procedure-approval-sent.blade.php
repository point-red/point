@extends('emails.template')

@section('content')
    <div class="title">New Approval Request</div>
    <br>
    <div class="body-text">
        Hello {{ $name }},
        <br>
        @if ($procedure->approval_action === 'destroy')
        There is a new deletion request just sent to you.
        @else
        There is a new procedure approval request just sent to you.
        @endif
        <br>
        <div>
            <table
                style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
                border="1">
                <thead>
                    <tr>
                        <th style="padding: .5rem">Code</th>
                        <th style="padding: .5rem">Name</th>
                        <th style="padding: .5rem">Purpose</th>
                        <th style="padding: .5rem">Content</th>
                        <th style="padding: .5rem">Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: .5rem">
                            {{ $procedure->code ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $procedure->name ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $procedure->purpose ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $procedure->content ?: '-' }}
                        </td>
                        <td style="padding: .5rem">
                            {{ $procedure->note ?: '-' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if (@$url)
        <div style="text-align: center">
            <a
                href="{{ $url }}?action=approve"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve
            </a>
            <a
                href="{{ $url }}?action=reject"
                target="_blank"
                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
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
