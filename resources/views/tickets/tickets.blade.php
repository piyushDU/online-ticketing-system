@extends('layouts.app')

@section('title', 'Ticket Sales')

@section('content')
<div class="container mt-5">
    <div class="row">
        @foreach($events as $event)
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">{{ $event->title }}</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">{{ $event->description }}</p>
                    <h6>Ticket Types:</h6>
                    <form>
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        <ul class="list-group mb-3">
                            @foreach($event->ticketTypes as $ticket)
                            <li class="list-group-item">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" data-ticket="{{ $ticket->id }}" data-id="{{$event->id}}" name="ticket_type_id{{$event->id}}"  id="ticket{{ $ticket->id }}" value="{{ $ticket->price }}" data-name="{{$ticket->name}}" {{ $event->ticket_availability ? '' : 'disabled' }}>
                                    <label class="form-check-label" for="ticket{{ $ticket->id }}">
                                        {{ $ticket->name }} - ${{ $ticket->price }}
                                        @if($event->ticket_availability)
                                        <span class="badge badge-success float-right">Available</span>
                                        @else
                                        <span class="badge badge-danger float-right">Sold Out</span>    
                                        @endif
                                    </label>
                                    
                                </div>
                            </li>
                            @endforeach
                        </ul>
                        <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-outline-secondary decrement-btn" data-id="{{ $event->id }}" data-target="#quantity{{ $event->id }}">-</button>
                        </div>
                        <input type="text" class="form-control quantity-input" style="max-width: 17%;" id="quantity{{ $event->id }}" name="ticket_quantities" value="0" min="0" max="{{ $event->max_tickets }}">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary increment-btn" data-id="{{ $event->id }}" data-target="#quantity{{ $event->id }}">+</button>
                        </div>
                        <div class="ml-3 total-price total-price{{ $event->id }}">$0.00</div>
                    </div>
                        <button type="button" class="btn btn-primary submit" data-id="{{ $event->id }}">Purchase Ticket</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
@section('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    $(document).ready(function() {
        var count = 0;   
        $(document).on('click','.increment-btn', function() {
            // let eventId = $(this).data('id');
            // let ticketCount = $("#quantity"+ eventId).val();
            // count = count + 1;
            // $("#quantity"+ eventId).val(count);
            var eventId = $(this).data('id');
            var targetInput = $("#quantity" + eventId);
            var currentValue = parseInt(targetInput.val()) || 0; // Default to 0 if input value is empty
            var maxValue = parseInt(targetInput.attr('max')) || 10; // Default max value (adjust as needed)

            if (currentValue < maxValue) {
                currentValue++;
                targetInput.val(currentValue);
                updateTotalPrice(currentValue, eventId);
            }
            });
        });
        
        $(document).on('click','.decrement-btn', function() {
            // let eventId = $(this).data('id');
            // let ticketCount = $("#quantity"+ eventId).val();
            // console.log('ticketCount', ticketCount);
            // count = count - 1;
            // console.log('count', count);
            // $("#quantity"+ eventId).val(count);
            var eventId = $(this).data('id');
            var targetInput = $("#quantity" + eventId);
            var currentValue = parseInt(targetInput.val()) || 0; // Default to 0 if input value is empty

            if (currentValue > 0) {
                currentValue--;
                targetInput.val(currentValue);
                updateTotalPrice(currentValue, eventId);
            }
        });

    $(document).on('input', '.quantity-input', function() {
        updateTotalPrice();
    });

    $(document).on('click', '.form-check-input', function() {
        let eventId = $(this).data('id');
        $('.total-price'+eventId).text(`$0.00`);
        $('#quantity'+eventId).val(0);
        $(this)
    });

    function updateTotalPrice(quantity, eventId) {
        var totalPrice = 0;
        let price = $(`input[name="ticket_type_id${eventId}"]:checked`).val();
        if(price) {
            console.log('price', price);
            let ticketPrice = $("#ticket"+eventId).val();
            let ticketType = $("#ticket"+eventId).data("name");

            console.log('ticketPrice', ticketPrice);
            console.log('ticketType', ticketType);

            var quantity = parseInt(quantity);
            console.log('quantity', quantity);
            totalPrice += quantity * parseInt(price);
            
            $('.total-price'+eventId).text(`$ ${totalPrice.toFixed(2)}`);
        }
    }
    var stripe = Stripe('{{ env('STRIPE_KEY') }}');
    $(document).on('click', '.submit', function() {
        let eventId = $(this).data("id");
        let ticketTypeId = $(`input[name="ticket_type_id${eventId}"]:checked`).data('ticket');
        var price = $(`input[name="ticket_type_id${eventId}"]:checked`).val();
        var quantity =  $('#quantity'+eventId).val();
        if(quantity <= 0) {
            alert('Please select Quantity');
            return;
        } else if(!price) {
            alert('Please select Ticket Type');
            return;
        }

        $.ajax({
                url: '{{ route('tickets.purchase') }}',
                method: 'POST',
                data: {eventId: eventId, ticketTypeId:ticketTypeId, price: price,  quantity: quantity},
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val(),
                },
                success: function(response) {
                    return stripe.redirectToCheckout({ sessionId: response.id });
                },
                error: function(xhr) {
                    alert('Error creating event: ' + xhr.responseText);
                }
            });
        
        console.log('eventId', eventId);
        console.log('ticketTypeId', ticketTypeId);
        console.log('price', price);
        console.log('quantity', quantity);
    });

</script>
@endsection