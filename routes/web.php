<?php

use App\Http\Controllers\FrontController;
use App\Http\Controllers\MidtransController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/test-email', function () {
    $transaction = \App\Models\Transaction::with('user')->latest()->first();
    Mail::to($transaction->user)->send(new \App\Mail\TransactionSuccess($transaction));
    return 'Email sent!';
});

Route::post('/logout', function () {
    Auth::logout();
    return redirect()->route('front.index');
})->name('logout');

Route::get('/', [FrontController::class, 'index'])->name('front.index');

Route::get('/transactions', [FrontController::class, 'transactions'])->name('front.transactions');
Route::get('/transaction/{id}', [FrontController::class, 'transactions_details'])->name('front.transaction.details');

Route::get('/details/{product:slug}', [FrontController::class, 'details'])->name('front.details');

Route::get('/booking/{product:slug}', [FrontController::class, 'booking'])->name('front.booking');
Route::post('/booking/{product:slug}/save', [FrontController::class, 'booking_save'])->name('front.booking_save');

// Route::get('/success-booking/{transaction}', [FrontController::class, 'success_booking'])->name('front.success.booking');

Route::post('/checkout/finish', [FrontController::class, 'checkout_store'])->name('front.checkout.store');

Route::get('/checkout/{product:slug}/payment', [FrontController::class, 'checkout'])->name('front.checkout');

Route::get('/category/{category:slug}', [FrontController::class, 'category'])->name('front.category');

Route::get('/brand/{brand:slug}/products', [FrontController::class, 'brand'])->name('front.brand');

Route::get('/booking/check', [FrontController::class, 'my_booking'])->name('front.my-booking');

Route::get('/testimonials', [FrontController::class, 'testimonials'])->name('front.testimonials');
Route::post('/testimonials/send', [FrontController::class, 'testimonials_send'])->name('front.testimonials.send');
Route::get('/testimonials-show/{testimonial}', [FrontController::class, 'testimonials_show'])->name('front.testimonials.show');

// Midtrans
// Route::post('/midtrans/callback', [MidtransController::class, 'notificationHandler']);
Route::get('/midtrans/finish', [MidtransController::class, 'finishRedirect']);
Route::get('/midtrans/unfinish', [MidtransController::class, 'unfinishRedirect']);
Route::get('/midtrans/error', [MidtransController::class, 'errorRedirect']);

Route::get('/payment/{id}', [FrontController::class, 'payment'])->name('front.payment');

