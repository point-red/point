@extends('emails.template')

@section('content')
    <div class="title">New Approval Request</div>
    <br>
    <div class="body-text">
        Hello {{ $name }},
        <br>
        There is a new instruction approval request just sent to you.
        <br>
        <table
            style="width: 100%; border-collapse: collapse;
                margin-top: 2rem; margin-bottom: 2rem"
            border="1">
            <tr>
                <th style="padding: .5rem">Name</th>
                <th style="padding: .5rem">Action</th>
                <th style="padding: .5rem">Action</th>
            </tr>
            <tbody>
                <tr>
                    <td style="padding: .5rem">
                    {{ $instruction->number }} - {{ $instruction->name }}
                    </td>
                    <td style="padding: .5rem">{{ $instruction->approval_action }}</td>
                    <td style="padding: .5rem; text-align: center">
                        @if (!$instruction->approved_at)
                        <div>
                            <a
                                href="{{ $url }}?id={{ $instruction->id }}&action=approve"
                                target="_blank"
                                style="background-color: #4CAF50; border: none; color: white; margin:4px 0; padding: 3px 5px; text-align: center; text-decoration: none; display: inline-block; font-size: 12px; ">
                                Review
                            </a>
                            {{-- <a
                                href="{{ $url }}?id={{ $instruction->id }}&action=reject"
                                target="_blank"
                                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin: 4px 0; padding: 3px 5px; text-align: center; text-decoration: none; display: inline-block; font-size: 12px; ">
                                Reject
                            </a> --}}
                        </div>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @foreach ($instruction->steps as $step)
                    <tr>
                        <td style="padding: .5rem">
                            ⇨
                            {{ $step->name }}
                        </td>
                        <td style="padding: .5rem">
                        {{ $step->approval_action }}
                        </td>
                        <td class="text-center">
                            <div style="text-align: center">
                                <a
                                    href="{{ $url }}?step_id={{ $step->id }}&action=approve"
                                    target="_blank"
                                    style="background-color: #4CAF50; border: none; color: white; margin:4px 0; padding: 3px 5px; text-align: center; text-decoration: none; display: inline-block; font-size: 12px; ">
                                    Review
                                </a>
                                {{-- <a
                                    href="{{ $url }}?step_id={{ $step->id }}&action=reject"
                                    target="_blank"
                                    style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin: 4px 0; padding: 3px 5px; text-align: center; text-decoration: none; display: inline-block; font-size: 12px; ">
                                    Reject
                                </a> --}}
                            </div>
                        </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if (!@$url)
        <p>
            Open your dashboard to check.
        </p>
        @endif
        <br>
    </div>
@stop
