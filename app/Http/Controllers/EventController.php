<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Event;
use App\Models\TicketType;
use App\Models\EventAttendee;
use DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Stripe\Checkout\Session as StripeSession;
use App\Models\Transaction;
use App\Events\PurchaseTicket;

class EventController extends Controller
{
    public function index()
    {
        // Fetch events for the logged-in user
        $events = Auth::user()->events()->orderBy('created_at', 'desc')->get();
        return view('events.index', compact('events'));
    }

    public function create()
    {
        return view('events.create');
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'ticket_availability' => 'required|integer|min:0',
            'ticket_types.*.name' => 'required|string|max:255',
            'ticket_types.*.price' => 'required|numeric|min:0',
        ]);

        $event = new Event([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'location' => $request->location,
            'ticket_availability' => $request->ticket_availability,
        ]);
        Auth::user()->events()->save($event);
    
        // Create ticket types
        foreach ($request->ticket_types as $ticketTypeData) {
            $ticketType = new TicketType([
                'name' => $ticketTypeData['name'],
                'price' => $ticketTypeData['price'],
            ]);
            $event->ticketTypes()->save($ticketType);
        }

        return response()->json(['message' => 'Event created successfully', 'event' => $event], 200);
    }

    public function edit(Event $event)
    {
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'location' => 'required|string|max:255',
            'ticket_availability' => 'required|integer|min:0',
            'ticket_types.*.id' => 'nullable|exists:ticket_types,id',
            'ticket_types.*.name' => 'required|string|max:255',
            'ticket_types.*.price' => 'required|numeric|min:0',
        ]);

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
            'location' => $request->location,
            'ticket_availability' => $request->ticket_availability,
        ]);

        $updatedTicketTypes = [];
        foreach ($request->ticket_types as $ticketTypeData) {
            $ticketTypeId = $ticketTypeData['id'] ?? null;
            $ticketType = TicketType::updateOrCreate(['id' => $ticketTypeId], [
                'event_id' => $event->id,
                'name' => $ticketTypeData['name'],
                'price' => $ticketTypeData['price'],
            ]);
            $updatedTicketTypes[] = $ticketType->id;
        }

        $event->ticketTypes()->whereNotIn('id', $updatedTicketTypes)->delete();

        return response()->json(['message' => 'Event updated successfully', 'event' => $event], 200);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(['message' => 'Event deleted successfully'], 200);
    }

    public function showTickets()
    {
        $events = Event::with('ticketTypes')->get();
        foreach ($events as $event) {
            $event->total_availability = $event->ticketTypes->sum('quantity');
        }
        return view('tickets.tickets', compact('events'));
    }

    public function attendees(Event $event)
    {
        $event->load('attendees.user', 'ticketTypes');
        return view('attendees.index', compact('event'));
    }
    
    public function purchaseTicket(Request $request)
    {
        $event_id = $request->eventId;
        $ticket_type_id = $request->ticketTypeId;
        $quantity = $request->quantity;
        $price = $request->price;

        // Retrieve event and ticket type
        $event = Event::findOrFail($event_id);
        $ticketType = TicketType::findOrFail($ticket_type_id);

        // Check if enough tickets are available
        if ($event->ticket_availability < $quantity) {
            return response()->json(['message' => 'Not enough tickets available.', 'event' => $event_id], 200);
        }

        // Begin a transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Calculate total price
            $totalPrice = $price * $quantity;

            // Save purchase details in event_attendees table
            $attendee = EventAttendee::create([
                'event_id' => $event_id,
                'ticket_type_id' => $ticket_type_id,
                'user_id' => Auth::id(),
                'quantity' => $quantity,
                'total_price' => $totalPrice,
            ]);
            // Decrease ticket availability
            $event->update(['ticket_availability' => $event->ticket_availability - $quantity]);

            // Commit the transaction
            DB::commit();
            return response()->json(['message' => 'Ticket purchased successfully!', 'event' => $event_id], 200);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            return back()->with('error', 'Error purchasing ticket: ' . $e->getMessage());
        }
    }

    public function purchase(Request $request)
    {
        event(new PurchaseTicket('New notification!'));
        $event_id = $request->eventId;
        $ticket_type_id = $request->ticketTypeId;
        $quantity = $request->quantity;
        $price = $request->price;
        $ticketType = TicketType::findOrFail($ticket_type_id);
        $totalPrice = $price * $quantity;
        Stripe::setApiKey(config("services.stripe.secret"));

        $session = Session::create([
            "payment_method_types" => ["card"],
            "line_items" => [
                [
                    "price_data" => [
                        "currency" => "usd",
                        "product_data" => ["name" => $ticketType->name],
                        "unit_amount" => $totalPrice * 100,
                    ],
                    "quantity" => $quantity,
                ],
            ],
            "mode" => "payment",
            'success_url' => route('payment.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
            "cancel_url" => route("payment.cancel"),
            'metadata' => [
                'event_id' => $event_id,
                'ticket_type_id' => $ticket_type_id,
                'quantity' => $quantity,
                'price' => $price,
                'user_id' => Auth::id(),
            ],
        ]);
        
        return response()->json(['id' => $session->id]);
    }

    public function success(Request $request)
    {
        $session_id = $request->query('session_id');

        if (!$session_id) {
            return redirect()->route('dashboard')->with('error', 'Invalid session ID');
        }
    
        Stripe::setApiKey(config('services.stripe.secret'));
    
        try {
            $session = StripeSession::retrieve($session_id);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('error', 'Unable to retrieve Stripe session');
        }
    
        $metadata = $session->metadata->toArray();
        $event_id = $metadata['event_id'] ?? null;
        $ticket_type_id = $metadata['ticket_type_id'] ?? null;
        $quantity = $metadata['quantity'] ?? null;
        $price = $metadata['price'] ?? null;
        $user_id = $metadata['user_id'] ?? null;
    
        if (!$event_id || !$ticket_type_id || !$quantity || !$price || !$user_id) {
            return redirect()->route('dashboard')->with('error', 'Missing metadata in Stripe session');
        }
    
        $event = Event::findOrFail($event_id);
        $ticketType = TicketType::findOrFail($ticket_type_id);
    
        if ($event->ticket_availability < $quantity) {
            return response()->json(['message' => 'Not enough tickets available.', 'event' => $event_id], 200);
        }
    
        DB::beginTransaction();
    
        try {
            $totalPrice = $price * $quantity;
    
            $attendee = EventAttendee::create([
                'event_id' => $event_id,
                'ticket_type_id' => $ticket_type_id,
                'user_id' => $user_id,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
            ]);
            
            Transaction::create([
                'user_id' => $user_id,
                'event_id' => $event_id,
                'ticket_type_id' => $ticket_type_id,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'stripe_session_id' => $session_id,
                'stripe_payment_intent_id' => $session->payment_intent,
                'stripe_payment_method_id' => $session->payment_method,
                'stripe_customer_id' => $session->customer,
                'stripe_payment_status' => $session->payment_status,
            ]);

            // Decrease ticket availability
            $event->update(['ticket_availability' => $event->ticket_availability - $quantity]);
    
            // Commit the transaction
            DB::commit();
            
            return view('tickets.success', ['event' => $event]);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('dashboard')->with('error', 'Error purchasing ticket: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return view("tickets.cancel");
    }
}
