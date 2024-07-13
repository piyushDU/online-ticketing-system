@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit Event: {{ $event->title }}</div>

                <div class="card-body">
                    <form id="edit-event-form">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="title">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ $event->title }}" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Event Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required>{{ $event->description }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="date">Event Date</label>
                            <input type="datetime-local" class="form-control" id="date" name="date" value="{{ \Carbon\Carbon::parse($event->date)->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="location">Event Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="{{ $event->location }}" required>
                        </div>

                        <div class="form-group">
                            <label for="ticket_availability">Tickets Available</label>
                            <input type="number" class="form-control" id="ticket_availability" name="ticket_availability" value="{{ $event->ticket_availability }}" required>
                        </div>

                        <hr>

                        <h4>Manage Ticket Types</h4>

                        <div id="ticket-types-container">
                            @foreach ($event->ticketTypes as $index => $ticketType)
                            <div class="ticket-type">
                                <div class="form-row">
                                    <div class="col">
                                        <input type="text" class="form-control" name="ticket_types[{{ $index }}][id]" value="{{ $ticketType->id }}" style="display: none;">
                                        <input type="text" class="form-control" name="ticket_types[{{ $index }}][name]" value="{{ $ticketType->name }}" placeholder="Ticket Type Name" required>
                                    </div>
                                    <div class="col">
                                        <input type="number" class="form-control" name="ticket_types[{{ $index }}][price]" value="{{ $ticketType->price }}" placeholder="Price" required>
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" class="btn btn-danger remove-ticket-type">&times;</button>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-success mt-2" id="add-ticket-type">Add Ticket Type</button>

                        <hr>

                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var ticketTypeIndex = {{ $event->ticketTypes->count() }};

        // Add ticket type fields dynamically
        $('#add-ticket-type').click(function() {
            var ticketTypeTemplate = `
                <div class="ticket-type mt-2">
                    <div class="form-row">
                        <div class="col">
                            <input type="text" class="form-control" name="ticket_types[${ticketTypeIndex}][id]" style="display: none;">
                            <input type="text" class="form-control" name="ticket_types[${ticketTypeIndex}][name]" placeholder="Ticket Type Name" required>
                        </div>
                        <div class="col">
                            <input type="number" class="form-control" name="ticket_types[${ticketTypeIndex}][price]" placeholder="Price" required>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-danger remove-ticket-type">&times;</button>
                        </div>
                    </div>
                </div>
            `;
            
            $('#ticket-types-container').append(ticketTypeTemplate);
            ticketTypeIndex++;
        });

        // Remove ticket type field
        $('#ticket-types-container').on('click', '.remove-ticket-type', function() {
            $(this).closest('.ticket-type').remove();
        });

        // Handle form submission
        $('#edit-event-form').on('submit', function(event) {
            event.preventDefault();

            $.ajax({
                url: '{{ route('events.update', $event->id) }}',
                method: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    alert(response.message);
                    window.location.href = '{{ route('events.index') }}';
                },
                error: function(xhr) {
                    alert('Error updating event: ' + xhr.responseText);
                }
            });
        });
    });
</script>
@endsection