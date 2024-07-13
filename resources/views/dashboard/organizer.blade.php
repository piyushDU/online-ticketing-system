@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Organizer Dashboard') }}</div>

                <div class="card-body">
                    <p>Welcome, {{ Auth::user()->name }}!</p>
                    <p>Your Role: {{ Auth::user()->role }}</p>

                    <h5>Organizer Actions:</h5>
                    <ul>
                        <li><a href="#">Manage Events</a></li>
                        <!-- Add more organizer-specific actions or links -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection