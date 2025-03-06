<?php

namespace Webkul\ParamPOS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SoapClient;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository   $orderRepository,
        protected InvoiceRepository $invoiceRepository
    )
    {
        //
    }

    /**
     * Redirects to the ParamPOS server.
     *
     * \Illuminate\Contracts\View\View
     * \Illuminate\Foundation\Application
     * \Illuminate\Contracts\View\Factory
     * \Illuminate\Contracts\Foundation\Application
     */
    public function redirect(): \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
    {
        $cart = Cart::getCart();
        $user = Customer::find($cart->customer_id);

        $terminal_id = "10738";
        $user_name = "Test";
        $password = "Test";
        $guid = "0c13d406-873b-403b-9c09-a5766840d98c";
        $gsm = $user->phone;
        $amount = number_format($cart->grand_total, 2, ',', '');
        $order_id = rand();
        $transactionId = Str::uuid()->toString();
        $installment = 0;
        $max_installment = 6;

        $callback_url = route('parampos.callback');

        $xml_data = '<?xml version="1.0" encoding="utf-8"?>
        <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
        xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <TP_Modal_Payment xmlns="https://turkpos.com.tr/">
                    <d>
                        <Code>' . $terminal_id . '</Code>
                        <User>' . $user_name . '</User>
                        <Pass>' . $password . '</Pass>
                        <GUID>' . $guid . '</GUID>
                        <GSM>' . $gsm . '</GSM>
                        <Amount>' . $amount . '</Amount>
                        <Order_ID>' . $order_id . '</Order_ID>
                        <TransactionId>' . $transactionId . '</TransactionId>
                        <Callback_URL>' . $callback_url . '</Callback_URL>
                        <MaxInstallment>' . $max_installment . '</MaxInstallment>
                    </d>
                </TP_Modal_Payment>
            </soap:Body>
        </soap:Envelope>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://test-dmz.param.com.tr/turkpos.ws/service_turkpos_test.asmx?op=TP_Modal_Payment");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: text/xml; charset=utf-8",
            "SOAPAction: https://turkpos.com.tr/TP_Modal_Payment"
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error: ' . curl_error($ch));
            die('cURL error: ' . curl_error($ch));
        }

        curl_close($ch);

        if (curl_errno($ch)) {
            Log::error('cURL error: ' . curl_error($ch));
            die('cURL error: ' . curl_error($ch));
        }

        $response = simplexml_load_string($result);

        Log::info("SOAP Response: " . $result);

        if ($response === false) {
            Log::error("SOAP Response parse error");
            die("SOAP Response parse error");
        }

        $response->registerXPathNamespace('ns1', 'https://turkpos.com.tr/');
        $url_element = $response->xpath('//ns1:TP_Modal_PaymentResponse/ns1:TP_Modal_PaymentResult/ns1:URL');

        if (isset($url_element[0])) {
            $iframe_url = (string) $url_element[0];
            return view('parampos::iframe', compact('iframe_url'));
        } else {
            Log::error("ParamPOS IFRAME failed");
            die("ParamPOS IFRAME failed: " . ($response->Body->Fault->Reason->Text ?? 'Unknown error'));
        }
    }


    /**
     * Redirects to the ParamPOS server.
     */
    public function callback(Request $request): \Illuminate\Http\RedirectResponse
    {
        //
    }

    /**
     * Place an order and redirect to the success page.
     *
     * @throws \Exception
     */
    public function success(): \Illuminate\Http\RedirectResponse
    {
        //
    }

    /**
     * Redirect to the cart page with error message.
     */
    public function failure(): \Illuminate\Http\RedirectResponse
    {
        //
    }
}
