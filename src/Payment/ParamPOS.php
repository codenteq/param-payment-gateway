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
     * Check if the payment method is available
     */
    public function isAvailable(): bool
    {
        return parent::isAvailable() && $this->hasValidCredentials();
    }

    /**
     * Get payment method title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getConfigData('title') ?? trans('parampos::app.parampos.system.title');
    }

    /**
     * Get payment method description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getConfigData('description') ?? trans('parampos::app.parampos.system.description');
    }

    /**
     * Get payment method image/logo.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : asset('vendor/parampos/images/parampos.svg');
    }

    /**
     * Get Client Code from configuration.
     *
     * @return string|null
     */
    public function getClientCode(): ?string
    {
        return $this->getConfigData('client_code');
    }

    /**
     * Get Client Username from configuration.
     *
     * @return string|null
     */
    public function getClientUsername(): ?string
    {
        return $this->getConfigData('client_username');
    }

    /**
     * Get Client Password from configuration.
     *
     * @return string|null
     */
    public function getClientPassword(): ?string
    {
        return $this->getConfigData('client_password');
    }

    /**
     * Get GUID from configuration.
     *
     * @return string|null
     */
    public function getGuid(): ?string
    {
        return $this->getConfigData('guid');
    }

    /**
     * Check if sandbox mode is enabled.
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return (bool) $this->getConfigData('sandbox');
    }

    /**
     * Get payment gateway URL based on environment.
     *
     * @return string
     */
    public function getPaymentUrl(): string
    {
        return $this->isSandbox()
            ? 'https://testposws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx'
            : 'https://posws.param.com.tr/turkpos.ws/service_turkpos_prod.asmx';
    }

    /**
     * Validate merchant credentials.
     *
     * @return bool
     */
    public function hasValidCredentials(): bool
    {
        return ! empty($this->getClientCode()) && ! empty($this->getClientUsername()) && ! empty($this->getClientPassword() && ! empty($this->getGuid()));
    }
}
