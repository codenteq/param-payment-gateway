<?php

namespace Webkul\ParamPOS\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class ParamPOS extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'parampos';

    public function getRedirectUrl(): string
    {
        return route('parampos.redirect');
    }

    /**
     * Returns payment method image.
     */
    public function getImage(): string
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : bagisto_asset('images/money-transfer.png', 'shop');
    }
}
