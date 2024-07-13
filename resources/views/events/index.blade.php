@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">My Events</div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <h3>Welcome, {{ Auth::user()->name }}!</h3>

                    @if (Auth::user()->role === 'organizer')
                        <p>You are logged in as an organizer.</p>
                        <a href="{{ route('events.create') }}" class="btn btn-primary">Create Event</a>
                    @elseif (Auth::user()->role === 'attendee')
                        <p>You are logged in as an attendee.</p>
                    @endif
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Tickets Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                            <tr>
                                <td>{{ $event->title }}</td>
                                <td>{{ $event->date->format('M d, Y H:i A') }}</td>
                                <td>{{ $event->location }}</td>
                                <td>{{ $event->ticket_availability }}</td>
                                <td>
                                    <a href="{{ route('events.edit', $event->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <button class="btn btn-sm btn-danger delete-event" data-id="{{ $event->id }}">Delete</button>
                                    <a href="{{ route('events.attendees', $event->id) }}" class="btn btn-sm btn-info">View Attendees</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.delete-event').on('click', function() {
            if (confirm('Are you sure you want to delete this event?')) {
                const eventId = $(this).data('id');

                $.ajax({
                    url: '/events/' + eventId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.message);
                        location.reload();
                    },
                    error: function(xhr) {
                        alert('Error deleting event: ' + xhr.responseText);
                    }
                });
            }
        });
    });
</script>
@endsection