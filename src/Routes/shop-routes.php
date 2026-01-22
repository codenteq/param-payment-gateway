<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Webkul\ParamPOS\Http\Controllers\PaymentController;

Route::group(['middleware' => ['web']], function () {
    Route::controller(PaymentController::class)->prefix('parampos')->group(function () {

        /**
         * ParamPOS payment routes
         */
        Route::get('/redirect', 'redirect')->name('parampos.redirect');

        Route::get('/success', 'success')->name('parampos.success');

        Route::get('/cancel', 'failure')->name('parampos.cancel');

        Route::post('/callback', 'callback')->name('parampos.callback')
            ->withoutMiddleware(VerifyCsrfToken::class);
    });
});
