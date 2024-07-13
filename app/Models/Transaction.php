<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'ticket_type_id',
        'quantity',
        'total_price',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'stripe_payment_method_id',
        'stripe_customer_id',
        'stripe_payment_status',
    ];
}
