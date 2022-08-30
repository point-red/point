@extends('emails.template')

@section('content')
    <div class="title">Approval Email</div>
    <br>
    <div class="body-text">
        Hello Mrs/Mr/Ms {{ $fullName }},
        <br>
        You Have an approval for Memo Journal . we would like to details as follows:
        <br>
        <div>
            <table
                style="width: 100%; border-collapse: collapse;
                    margin-top: 2rem; margin-bottom: 2rem"
                border="0">
                <tbody>
                    <tr>
                        <td style="width: 25%">Form Number</td>
                        <td>: {{ $form['number'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Form Date</td>
                        <td>: {{ $form['date'] ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="width: 25%">Create at</td>
                        <td>: {{ $form['created'] ?: '-' }}</td>
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
                        <th style="padding: .5rem">Account</th>
                        <th style="padding: .5rem">Master</th>
                        <th style="padding: .5rem">References</th>
                        <th style="padding: .5rem">Notes</th>
                        <th style="padding: .5rem">Debit</th>
                        <th style="padding: .5rem">Credit</th>
                        <th style="padding: .5rem">Created By</th>
                        <th style="padding: .5rem">Created At</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($memoJournals as $memoJournal)
                    @foreach($memoJournal->items as $key=>$memoJournalItem)
                        <tr>
                            @if ($key == 0)
                                <td rowspan="{{count($memoJournal->items)}}" style="padding: .5rem">
                                    {{ $memoJournal->no }}
                                </td>
                                <td rowspan="{{count($memoJournal->items)}}" style="padding: .5rem">
                                    {{ date('d F Y', strtotime($memoJournal->form->date)) ?: '-' }}
                                </td>
                                <td rowspan="{{count($memoJournal->items)}}" style="padding: .5rem">
                                    {{ $memoJournal->form->number ?: '-' }}
                                </td>
                            @endif
                            <td style="padding: .5rem">
                                {{ $memoJournalItem->chart_of_account_name ?: '-' }}
                            </td>
                            <td style="padding: .5rem">
                                @if ($memoJournalItem->masterable)
                                    {{ $memoJournalItem->masterable->name ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="padding: .5rem">
                                @if ($memoJournalItem->form)
                                    {{ $memoJournalItem->form->number ?: '-' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td style="padding: .5rem">
                                {{ $memoJournalItem->notes ?: '-' }}
                            </td>
                            <td style="padding: .5rem">
                                {{ $memoJournalItem->debit ?: '-' }}
                            </td>
                            <td style="padding: .5rem">
                                {{ $memoJournalItem->credit ?: '-' }}
                            </td>
                            @if ($key == 0)
                                <td rowspan="{{count($memoJournal->items)}}" style="padding: .5rem">
                                    {{ $memoJournal->created_by ?: '-' }}
                                </td>
                                <td rowspan="{{count($memoJournal->items)}}" style="padding: .5rem">
                                    {{ date('d F Y', strtotime($memoJournal->created_at)) ?: '-' }}
                                </td>
                                <td rowspan="{{count($memoJournal->items)}}" style="padding: .5rem">
                                    <div style="text-align: center">
                                        <a
                                            href="{{ $url ?: '-' }}accounting/memo-journal/{{ $memoJournal->id }}?approver_id={{ $approverId }}&token={{ $token }}"
                                            target="_blank"
                                            style="background-color: rgb(192, 192, 192); border: none; color: black; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                            Check
                                        </a>
                                        <a
                                            href="{{ $url ?: '-' }}approval?crud-type={{ $memoJournal->action }}&resource-type=MemoJournal&action=approve&ids={{ $memoJournal->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                                            target="_blank"
                                            style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                            Approve
                                        </a>
                                        <a
                                            href="{{ $url ?: '-' }}approval?crud-type={{ $memoJournal->action }}&resource-type=MemoJournal&action=reject&ids={{ $memoJournal->id }}&approver_id={{ $approverId }}&token={{ $token }}"
                                            target="_blank"
                                            style="background-color: rgb(255, 0, 0); border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                                            Reject
                                        </a>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
        @if (@$url)
        <div style="text-align: center">
            <a
                href="{{ $url }}approval-all?resource-type=MemoJournal&action=approve&ids={{ $ids }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Approve All
            </a>
            <a
                href="{{ $url }}approval-all?resource-type=MemoJournal&action=reject&ids={{ $ids }}&approver_id={{ $approverId }}&token={{ $token }}"
                target="_blank"
                style="background-color: rgb(238, 238, 238); border: none; color: rgb(83, 83, 83); margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">
                Reject All
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
