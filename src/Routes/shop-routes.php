<?php

use Illuminate\Support\Facades\Route;
use Webkul\ParamPOS\Http\Controllers\PaymentController;

Route::group(['middleware' => ['web']], function () {

    /**
     * ParamPOS payment routes
     */
    Route::get('/parampos-redirect', [PaymentController::class, 'redirect'])->name('parampos.redirect');

    Route::post('/parampos-callback', [PaymentController::class, 'callback'])->name('parampos.callback');

});
