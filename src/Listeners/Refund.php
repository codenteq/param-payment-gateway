<?php

namespace Webkul\ParamPOS\Listeners;

use Webkul\Admin\Listeners\Base;
use Webkul\Admin\Mail\Order\RefundedNotification;
use Webkul\ParamPOS\Payment\ParamPOS;

class Refund extends Base
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ParamPOS $paramPOS
    ) {
        //
    }

    /**
     * After order is created
     */
    public function afterCreated(\Webkul\Sales\Contracts\Refund $refund): void
    {
        $this->refundOrder($refund);

        try {
            if (! core()->getConfigData('emails.general.notifications.emails.general.notifications.new_refund')) {
                return;
            }

            $this->prepareMail($refund, new RefundedNotification($refund));
        } catch (\Exception $e) {
            report($e);
        }
    }

    /**
     * After Refund is created
     */
    public function refundOrder(\Webkul\Sales\Contracts\Refund $refund): void
    {
        $order = $refund->order;

        if ($order->payment->method === 'parampos') {
            $terminalId = $this->paramPOS->getClientCode();
            $username = $this->paramPOS->getClientUsername();
            $password = $this->paramPOS->getClientPassword();
            $guid = $this->paramPOS->getGuid();

            $orderId = $order->payment['additional'] ?? null;
            $amount = number_format($refund->grand_total, 2, '.', '');

            $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <TP_Islem_Iptal_Iade_Kismi2 xmlns="https://turkpos.com.tr/">
      <G>
        <CLIENT_CODE>$terminalId</CLIENT_CODE>
        <CLIENT_USERNAME>$username</CLIENT_USERNAME>
        <CLIENT_PASSWORD>$password</CLIENT_PASSWORD>
      </G>
      <GUID>$guid</GUID>
      <Durum>IADE</Durum>
      <Siparis_ID>{$orderId}</Siparis_ID>
      <Tutar>{$amount}</Tutar>
    </TP_Islem_Iptal_Iade_Kismi2>
  </soap:Body>
</soap:Envelope>
XML;

            $xml = str_replace(['{', '}'], '', $xml);

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL            => $this->paramPOS->getPaymentUrl() . '?op=TP_Islem_Iptal_Iade_Kismi2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $xml,
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: https://turkpos.com.tr/TP_Islem_Iptal_Iade_Kismi2',
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);

            $response = curl_exec($ch);

            curl_close($ch);

            $xmlResponse = simplexml_load_string($response);
            $xmlResponse->registerXPathNamespace('ns', 'https://turkpos.com.tr/');
        }
    }
}
