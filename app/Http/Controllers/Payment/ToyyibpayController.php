<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use App\Models\Order;
use Session;
use Auth;


class ToyyibpayController extends Controller
{
    public function pay()
    {
        $amount=0;
        if(Session::has('payment_type')){
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');

            if($paymentType == 'cart_payment'){
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = round($combined_order->grand_total * 100);
                $combined_order_id = $combined_order->id;
                $billname = 'Ecommerce Cart Payment';
                $first_name = json_decode($combined_order->shipping_address)->name;
                $phone = json_decode($combined_order->shipping_address)->phone;
                $email = json_decode($combined_order->shipping_address)->email;
            }
            elseif($paymentType == 'order_re_payment'){
                $order = Order::findOrFail($paymentData['order_id']);
                $amount = round($order->grand_total * 100);
                $combined_order_id = $order->id;
                $billname = 'Ecommerce Order Payment';
                $first_name = json_decode($order->shipping_address)->name;
                $phone = json_decode($order->shipping_address)->phone;
                $email = json_decode($order->shipping_address)->email;
            }
            elseif ($paymentType == 'wallet_payment') {
                $amount = $paymentData['amount'] * 100;
                $combined_order_id = rand(10000,99999);
                $billname = 'Wallet Payment';
                $first_name = Auth::user()->name;
                $phone = (Auth::user()->phone != null) ? Auth::user()->phone : '123456789';
                $email = (Auth::user()->email != null) ? Auth::user()->email : 'example@example.com';

            }
            elseif ($paymentType == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $amount = round($customer_package->amount * 100);
                $combined_order_id = rand(10000,99999);
                $billname = 'Customer Package Payment';
                $first_name = Auth::user()->name;
                $phone = (Auth::user()->phone != null) ? Auth::user()->phone : '123456789';
                $email = (Auth::user()->email != null) ? Auth::user()->email : 'example@example.com';
            }
            elseif ($paymentType == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);
                $amount = round($seller_package->amount * 100);
                $combined_order_id = rand(10000,99999);
                $billname = 'Seller Package Payment';
                $first_name = Auth::user()->name;
                $phone = (Auth::user()->phone != null) ? Auth::user()->phone : '123456789';
                $email = (Auth::user()->email != null) ? Auth::user()->email : 'example@example.com';
            }
        }

        
        $option = array(
            'userSecretKey' => config('toyyibpay.key'),
            'categoryCode' => config('toyyibpay.category'),
            'billName' =>  $billname,
            'billDescription' => 'Payment Using ToyyibPay',
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount'=> $amount,
            'billReturnUrl'=> route('toyyibpay-status'),
            'billCallbackUrl' => route('toyyibpay-callback'),
            'billExternalReferenceNo' => $combined_order_id,
            'billTo' => $first_name,
            'billEmail' => $email,
            'billPhone'=> $phone,
            'billSplitPayment' => 0,
            'billSplitPaymentArgs'=>'',
            'billPaymentChannel' => 2,
            'billContentEmail'=>'Thank you for purchasing our product!',
            'billChargeToCustomer'=> 2
        );

        if(get_setting('toyyibpay_sandbox') == 1)
        $site_url='https://dev.toyyibpay.com/';
        else
        $site_url='https://toyyibpay.com/';

        $url = $site_url.'index.php/api/createBill';
        $response = Http::asForm()->post($url, $option);
        $billcode = $response[0]['BillCode'];
        $final_url = $site_url . $billcode;
        return redirect($final_url);

    }


    public function paymentstatus()
    {

        $response= request()->status_id;
        if($response == 1)
        {
            $payment = ["status" => "Success"];
            $payment_type = Session::get('payment_type');
            $paymentData = session()->get('payment_data');

            if ($payment_type == 'cart_payment') {
                flash(translate("Your order has been placed successfully"))->success();
                return (new CheckoutController)->checkout_done(session()->get('combined_order_id'), json_encode($payment));;
            }
            elseif ($payment_type == 'order_re_payment') {
                return (new CheckoutController)->orderRePaymentDone($paymentData, json_encode($payment));
            }
            elseif ($payment_type == 'wallet_payment') {
                return (new WalletController)->wallet_payment_done($paymentData, json_encode($payment));
            }
            elseif ($payment_type == 'customer_package_payment') {
                return (new CustomerPackageController)->purchase_payment_done($paymentData, json_encode($payment));
            }
            elseif ($payment_type == 'seller_package_payment') {
                return (new SellerPackageController)->purchase_payment_done($paymentData, json_encode($payment));
            }
        }
        else
            {
                flash(translate('Payment is cancelled'))->error();
                return redirect()->route('home');   
            }
        
    
    }

    public function callback()
    {

       $response= request()->all(['refno','status','reason','billcode','order_id','amount']);
       Log::info($response);
    }
}


