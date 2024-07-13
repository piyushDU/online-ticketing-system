@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Attendees for {{ $event->title }}</h1>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Ticket Type</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($event->attendees as $attendee)
                    <tr>
                        <td>{{ $attendee->user->name }}</td>
                        <td>{{ $attendee->user->email }}</td>
                        <td>{{ $attendee->ticketType->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
