@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

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
                            <a href="{{ route('tickets') }}" class="btn btn-primary">View Ticket</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection