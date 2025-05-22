<?php

namespace Webkul\ParamPOS\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     */
    protected array $listen = [
        'sales.refund.save.after' => [
            'Webkul\ParamPOS\Listeners\Refund@afterCreated',
        ],
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen('sales.refund.save.after', 'Webkul\ParamPOS\Listeners\Refund@afterCreated');
    }
}
