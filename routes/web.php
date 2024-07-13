<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [AuthController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:organizer'])->group(function () {
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::get('events/{event}/attendees', [EventController::class, 'attendees'])->name('events.attendees');
    
});

Route::middleware(['auth', 'role:attendee'])->group(function () {
    Route::get('/tickets', [EventController::class, 'showTickets'])->name('tickets');
    Route::post("/purchase", [EventController::class, "purchase"])->name("tickets.purchase");
    Route::get("/payment/success", [EventController::class, "success"])->name("payment.success");
    Route::get("/payment/cancel", [EventController::class, "cancel"])->name("payment.cancel");
});


Route::get('/', function () {
    return view('welcome');
});
