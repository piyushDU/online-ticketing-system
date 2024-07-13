<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('event_id');
                $table->unsignedBigInteger('ticket_type_id');
                $table->integer('quantity');
                $table->decimal('total_price', 8, 2);
                $table->string('stripe_session_id');
                $table->string('stripe_payment_intent_id')->nullable();
                $table->string('stripe_payment_method_id')->nullable();
                $table->string('stripe_customer_id')->nullable();
                $table->string('stripe_payment_status')->nullable();
                $table->timestamps();
    
                // Foreign key constraints
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
                $table->foreign('ticket_type_id')->references('id')->on('ticket_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
