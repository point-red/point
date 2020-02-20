@extends('emails.template')

@section('content')
    <div class="title">{{ $project->name }}</div>
    <br>
    <div class="body-text">
        Hello {{ $name }},
        <br>
        {{ $user->first_name }} {{ $user->last_name }} You have invited to join {{ strtolower($project->code) }}.cloud.point.red
        <br>
        <a href="https://point.red/signup" style="background-color: #4CAF50; border: none; color: white; margin:8px 0; padding: 8px 16px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px; ">Accept Invitation</a>
        <br>
    </div>
@stop
