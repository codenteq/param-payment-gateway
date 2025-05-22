<?php

namespace Webkul\ParamPOS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Webkul\Checkout\Facades\Cart;
use Webkul\Customer\Models\Customer;
use Webkul\Sales\Models\OrderPayment;
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
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {
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
    public function redirect(): \Illuminate\Contracts\View\View
    {
        $cart = Cart::getCart();
        $user = Customer::find($cart->customer_id);

        $terminalId = env('PARAMPOS_CLIENT_CODE', 'null');
        $username = env('PARAMPOS_CLIENT_USERNAME', 'null');
        $password = env('PARAMPOS_CLIENT_PASSWORD', 'null');
        $guid = env('PARAMPOS_GUID', 'null');
        $gsm = $user->phone;
        $amount = number_format($cart->grand_total, 2, ',', '');
        $orderId = rand();
        $transactionId = (string) Str::uuid();
        $callbackUrl = route('parampos.callback');
        $maxInstallment = 0;

        $xml = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
    <soap:Body>
        <TP_Modal_Payment xmlns="https://turkpos.com.tr/">
            <d>
                <Code>{$terminalId}</Code>
                <User>{$username}</User>
                <Pass>{$password}</Pass>
                <GUID>{$guid}</GUID>
                <GSM>{$gsm}</GSM>
                <Amount>{$amount}</Amount>
                <Order_ID>{$orderId}</Order_ID>
                <TransactionId>{$transactionId}</TransactionId>
                <Callback_URL>{$callbackUrl}</Callback_URL>
                <MaxInstallment>{$maxInstallment}</MaxInstallment>
            </d>
        </TP_Modal_Payment>
    </soap:Body>
</soap:Envelope>
XML;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => env('PARAMPOS_BASE_URL', null).'?op=TP_Modal_Payment',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $xml,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: https://turkpos.com.tr/TP_Modal_Payment',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $message = curl_error($ch);
            curl_close($ch);
            abort(500, 'Connection error: '.$message);
        }

        curl_close($ch);

        $response = simplexml_load_string($result);

        if ($response === false) {
            abort(500, 'Invalid XML response received from ParamPOS');
        }

        $response->registerXPathNamespace('ns1', 'https://turkpos.com.tr/');
        $urlElement = $response->xpath('//ns1:TP_Modal_PaymentResponse/ns1:TP_Modal_PaymentResult/ns1:URL');

        if (! empty($urlElement[0])) {
            $iframeUrl = (string) $urlElement[0];

            return view('parampos::iframe', compact('iframeUrl'));
        }

        $error = (string) ($response->xpath('//soap:Fault/faultstring')[0] ?? 'Unknown error');
        abort(500, 'IFRAME URL not found in ParamPOS response: '.$error);
    }

    /**
     * Redirects to the ParamPOS server.
     */
    public function callback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $status = $request->input('TURKPOS_RETVAL_Sonuc');

        $paymentOrderId = $request->input('TURKPOS_RETVAL_Siparis_ID');

        if ($status === '1') {
            session(['payment_order_id' => $paymentOrderId]);

            return redirect()->route('parampos.success');
        } else {
            return redirect()->route('parampos.cancel');
        }

    }

    /**
     * Place an order and redirect to the success page.
     *
     * @throws \Exception
     */
    public function success(): \Illuminate\Http\RedirectResponse
    {
        $cart = Cart::getCart();

        $data = (new OrderResource($cart))->jsonSerialize();

        $order = $this->orderRepository->create($data);

        $this->savePaymentOrderId($order['id']);

        if ($order->canInvoice()) {
            $this->invoiceRepository->create($this->prepareInvoiceData($order));
        }

        Cart::deActivateCart();

        session()->flash('order_id', $order->id);

        return redirect()->route('shop.checkout.onepage.success');
    }

    /**
     * Redirect to the cart page with error message.
     */
    public function failure(): \Illuminate\Http\RedirectResponse
    {
        session()->flash('error', 'Param payment was either cancelled or the transaction failed.');

        return redirect()->route('shop.checkout.cart.index');
    }

    /**
     * Prepares order's invoice data for creation.
     */
    protected function prepareInvoiceData($order): array
    {
        $invoiceData = [
            'order_id' => $order->id,
            'invoice'  => ['items' => []],
        ];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    /**
     * Saves the payment transaction ID to the database.
     */
    protected function savePaymentOrderId(int $orderId): void
    {
        OrderPayment::where('order_id', $orderId)->update(['additional' => session('payment_order_id')]);
    }
}
