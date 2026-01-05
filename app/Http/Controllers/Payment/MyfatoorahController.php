<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\SellerPackageController;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\SellerPackage;
use Illuminate\Http\Request;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use Session;
use Redirect;

class MyfatoorahController extends Controller
{
    /**
     * @var array
     */
    public $mfConfig = [];

    /**
     * Initiate MyFatoorah Configuration
     */
    public function __construct() {
        $this->mfConfig = [
            'apiKey'      => env('MYFATOORAH_TOKEN'),
            'isTest'      => get_setting('myfatoorah_sandbox') == 1 ? true : false,
            'countryCode' => env('MYFATOORAH_COUNTRY_ISO'),
        ];
    }

    /**
     * Create MyFatoorah invoice
     *
     * @return \Illuminate\Http\Response
     */

    public function pay(Request $request)
    {
        $user = auth()->user();
        $payment_type =  Session::get('payment_type');
        $paymentData =  Session::get('payment_data');

        if (Session::has('payment_type')) {
            if ($payment_type == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail($request->session()->get('combined_order_id'));
                $amount = $combined_order->grand_total;
                $CustomerReference =  $payment_type . '-' . $combined_order->id . '-' . $user->id;
            } elseif ($payment_type == 'order_re_payment') {
                $order = Order::findOrFail($paymentData['order_id']);
                $amount = $order->grand_total;
                $CustomerReference =  $payment_type . '-' . $order->id . '-' . $user->id;
            } elseif ($payment_type == 'wallet_payment') {
                $amount = $paymentData['amount'];
                $CustomerReference = $user->id . '' . $amount;
                $CustomerReference = $payment_type . '-' . $amount . '-' . $user->id;
            } elseif ($payment_type == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $amount = $customer_package->amount;
                $CustomerReference = $user->id . '' . $customer_package->id;
                $CustomerReference =  $payment_type . '-' . $customer_package->id . '-' . $user->id;
            } elseif ($payment_type == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);
                $amount = $seller_package->amount;
                $CustomerReference = $user->id . '' . $seller_package->id;
                $CustomerReference =  $payment_type . '-' . $seller_package->id . '-' . $user->id;
            }
        }


        $currency_code = \App\Models\Currency::find(get_setting('system_default_currency'))->code;
        $paymentMethodId = 0;
        $callbackURL = route('myfatoorah.callback');

        $data = [
            'InvoiceValue'       => $amount,
            'DisplayCurrencyIso' => $currency_code,
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            'paymentMethodId'    => $paymentMethodId,
            'CustomerName'       => $user->name,
            'InvoiceValue'       => $amount,
            'DisplayCurrencyIso' => $currency_code,
            'CustomerEmail'      => $user->email ?? 'test@test.com',
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            'MobileCountryCode'  => '+965',
            'CustomerMobile'     => '12345678',
            'Language'           => 'en',
            'CustomerReference'  => $CustomerReference,

        ];
        try {
            $mfObj   = new MyFatoorahPayment($this->mfConfig);
            $data = $mfObj->getInvoiceURL($data, $paymentMethodId);
            if ($data['invoiceId']) {
                $checkoutUrl = $data['invoiceURL'];
                return Redirect::to($checkoutUrl);
            }
            flash(translate('Payment was failed'))->error();
            return redirect()->route('home');
        } catch (\Exception $e) {
            flash(translate('Payment was failed'))->error();
            return redirect()->route('home');
        }
    }

    /**
     * Get MyFatoorah payment information
     * 
     * @return \Illuminate\Http\Response
     */

    public function callback()
    {
        try {
            $mfObj   = new MyFatoorahPaymentStatus($this->mfConfig);
            $response = $mfObj->getPaymentStatus(request('paymentId'), 'PaymentId');

            if ($response->InvoiceStatus == 'Paid') {
                $payment_type = Session::get('payment_type');
                $paymentData = session()->get('payment_data');

                if ($payment_type == 'cart_payment') {
                    return (new CheckoutController)->checkout_done(session()->get('combined_order_id'), json_encode($response));
                } elseif ($payment_type == 'order_re_payment') {
                    return (new CheckoutController)->orderRePaymentDone($paymentData, json_encode($response));
                } elseif ($payment_type == 'wallet_payment') {
                    return (new WalletController)->wallet_payment_done($paymentData, json_encode($response));
                } elseif ($payment_type == 'customer_package_payment') {
                    return (new CustomerPackageController)->purchase_payment_done($paymentData, json_encode($response));
                } elseif ($payment_type == 'seller_package_payment') {
                    return (new SellerPackageController)->purchase_payment_done($paymentData, json_encode($response));
                }
            } else {
                flash(translate('Payment was failed'))->error();
                return redirect()->route('home');
            }
        } catch (\Exception $e) {
            Session::forget('payment_data');
            flash(translate('Payment was failed'))->error();
            return redirect()->route('home');
        }
    }
}
