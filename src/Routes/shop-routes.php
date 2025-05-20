<?php

use Illuminate\Support\Facades\Route;
use Webkul\ParamPOS\Http\Controllers\PaymentController;

Route::group(['middleware' => ['web']], function () {

    /**
     * ParamPOS payment routes
     */
    Route::get('/parampos-redirect', [PaymentController::class, 'redirect'])->name('parampos.redirect');

    Route::get('/parampos-success', [PaymentController::class, 'success'])->name('parampos.success');

    Route::get('/parampos-cancel', [PaymentController::class, 'failure'])->name('parampos.cancel');

    Route::post('/parampos-callback', [PaymentController::class, 'callback'])->name('parampos.callback');
});
