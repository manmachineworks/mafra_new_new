<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\Order;
use App\Models\SellerPackage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Session;


class PhonepeController extends Controller
{

    public function pay()
    {
        $phonepeVersion = get_setting('phonepe_version', '1');
        return $this->{"payByV$phonepeVersion"}();
    }

    public function payByV1()
    {
        if (Session::has('payment_type')) {
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');

            if ($paymentType == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = $combined_order->grand_total;
                $merchantTransactionId = $paymentType . '-' . $combined_order->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'order_re_payment') {
                $order = Order::findOrFail($paymentData['order_id']);
                $amount = $order->grand_total;
                $merchantTransactionId = $paymentType . '-' . $order->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'wallet_payment') {
                $amount = $paymentData['amount'];
                $merchantTransactionId = $paymentType . '-' . auth()->id() . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $amount = $customer_package->amount;
                $merchantTransactionId = $paymentType . '-' . $customer_package->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);
                $amount = $seller_package->amount;
                $merchantTransactionId = $paymentType . '-' . $seller_package->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            }
        }

        // $merchantTransactionId = "MT7850590068188104";
        $merchantId = env('PHONEPE_MERCHANT_ID');
        $salt_key = env('PHONEPE_SALT_KEY');
        $salt_index = env('PHONEPE_SALT_INDEX');


        $base_url = (get_setting('phonepe_sandbox') == 1) ? "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay" : "https://api.phonepe.com/apis/hermes/pg/v1/pay";

        $post_field = [
            'merchantId' => $merchantId,
            'merchantTransactionId' => $merchantTransactionId,
            'merchantUserId' => $merchantUserId,
            'amount' => $amount * 100,
            'redirectUrl' => route('phonepe.redirecturl'),
            'redirectMode' => 'POST',
            'callbackUrl' =>  route('phonepe.callbackUrl'),
            'mobileNumber' =>  "9999999999",
            "paymentInstrument" => [
                "type" => "PAY_PAGE"
            ],
        ];
        // dd($post_field);
        $payload = base64_encode(json_encode($post_field));

        $hashedkey =  hash('sha256', $payload . "/pg/v1/pay" . $salt_key) . '###' . $salt_index;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-VERIFY: ' . $hashedkey . '',
            'accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "\n{\n  \"request\": \"$payload\"\n}\n");

        $response = curl_exec($ch);
        $res = (json_decode($response));
        // dd($res);
        return Redirect::to($res->data->instrumentResponse->redirectInfo->url);
    }

    public function payByV2()
    {
        if (Session::has('payment_type')) {
            $paymentType = Session::get('payment_type');
            $paymentData = Session::get('payment_data');

            if ($paymentType == 'cart_payment') {
                $combined_order = CombinedOrder::findOrFail(Session::get('combined_order_id'));
                $amount = $combined_order->grand_total;
                $merchantTransactionId = $paymentType . '-' . $combined_order->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'order_re_payment') {
                $order = Order::findOrFail($paymentData['order_id']);
                $amount = $order->grand_total;
                $merchantTransactionId = $paymentType . '-' . $order->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'wallet_payment') {
                $amount = $paymentData['amount'];
                $merchantTransactionId = $paymentType . '-' . auth()->id() . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'customer_package_payment') {
                $customer_package = CustomerPackage::findOrFail($paymentData['customer_package_id']);
                $amount = $customer_package->amount;
                $merchantTransactionId = $paymentType . '-' . $customer_package->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            } elseif ($paymentType == 'seller_package_payment') {
                $seller_package = SellerPackage::findOrFail($paymentData['seller_package_id']);
                $amount = $seller_package->amount;
                $merchantTransactionId = $paymentType . '-' . $seller_package->id . '-' . auth()->id() . '-' . rand(0, 100000);
                $merchantUserId = auth()->id();
            }
        }
        Session::put('phonepe_txn_id', $merchantTransactionId);

        // Determine base URLs based on sandbox mode
        $isSandbox = get_setting('phonepe_sandbox') == 1;
        $tokenUrl = $isSandbox
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'
            : 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';

        $payUrl = $isSandbox
            ? 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay'
            : 'https://api.phonepe.com/apis/pg/checkout/v2/pay';

        // Get OAuth2 Token
        $tokenResponse = Http::asForm()->post($tokenUrl, [
            'client_id' => env('PHONEPE_CLIENT_ID'),
            'client_secret' => env('PHONEPE_CLIENT_SECRET'),
            'grant_type' => 'client_credentials',
            'client_version' => env('PHONEPE_CLIENT_VERSION'),
        ]);

        $tokenData = $tokenResponse->json();

        if (!$tokenResponse->successful() || empty($tokenData['access_token'])) {
            \Log::error('PhonePe V2 Token Error', ['response' => $tokenData]);
            abort(500, 'PhonePe authentication failed');
        }

        $accessToken = $tokenData['access_token'];
        Session::put('phonepe_access_token', $accessToken);
        $payload = [
            'merchantOrderId' => $merchantTransactionId,
            'merchantUserId' => $merchantUserId,
            'amount' => $amount * 100,
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'message' => 'Proceeding with payment',
                'merchantUrls' => [
                    'redirectUrl' => route('phonepe.redirecturl'),
                ],
            ],
            'paymentInstrument' => [
                'type' => 'PAY_PAGE',
            ],
        ];

        $payResponse = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'O-Bearer ' . $accessToken,
        ])->post($payUrl, $payload);

        $payData = $payResponse->json();
        if ($payResponse->successful() && isset($payData['redirectUrl'])) {
            return Redirect::to($payData['redirectUrl']);
        }
    }


    public function phonepe_redirecturl(Request $request)
    {
        $phonepeVersion = get_setting('phonepe_version', '1');
        if ($phonepeVersion == 1) {
            $payment_type = explode("-", $request['transactionId']);
            auth()->login(User::findOrFail($payment_type[2]));
            // dd($payment_type[0], $payment_type[1], $request['merchantId'], $request['transactionId'], $request->all());

            if ($request['code'] == 'PAYMENT_SUCCESS') {

                if ($payment_type[0] == 'cart_payment') {
                    Session::put('combined_order_id', $payment_type[1]);
                    return redirect()->route('order_confirmed');
                } elseif ($payment_type[0] == 'order_re_payment') {
                    flash(translate('Order Re-Payment Done'))->success();
                    return redirect()->route('home');
                } elseif ($payment_type[0] == 'wallet_payment') {
                    flash(translate('Recharge completed'))->success();
                    return redirect()->route('wallet.index');
                } elseif ($payment_type[0] == 'customer_package_payment') {
                    flash(translate('Package purchasing successful'))->success();
                    return redirect()->route('dashboard');
                } elseif ($payment_type[0] == 'seller_package_payment') {
                    flash(translate('Package purchasing successful'))->success();
                    return redirect()->route('seller.dashboard');
                }
            }
        } elseif ($phonepeVersion == 2) {
            $moid = Session::get('phonepe_txn_id');
            $accessToken =   Session::get('phonepe_access_token');
            $statusData = $this->getPhonePeOrderStatus($moid, $accessToken);
            $decoded_data = $statusData->getData();
            $payment_type = explode("-", $moid);
            auth()->login(User::findOrFail($payment_type[2]));
            Session::forget('phonepe_txn_id');
            Session::forget('phonepe_access_token');
            if ($decoded_data->data->state == 'COMPLETED') {
                if ($payment_type[0] == 'cart_payment') {
                    Session::put('combined_order_id', $payment_type[1]);
                    return redirect()->route('order_confirmed');
                } elseif ($payment_type[0] == 'order_re_payment') {
                    flash(translate('Order Re-Payment Done'))->success();
                    return redirect()->route('home');
                } elseif ($payment_type[0] == 'wallet_payment') {
                    flash(translate('Recharge completed'))->success();
                    return redirect()->route('wallet.index');
                } elseif ($payment_type[0] == 'customer_package_payment') {
                    flash(translate('Package purchasing successful'))->success();
                    return redirect()->route('dashboard');
                } elseif ($payment_type[0] == 'seller_package_payment') {
                    flash(translate('Package purchasing successful'))->success();
                    return redirect()->route('seller.dashboard');
                }
            }
        }

        flash(translate('Payment failed'))->success();
        return redirect()->back();
    }

    public function phonepe_callbackUrl(Request $request)
    {
        //
        $res = $request->all();
        $response = $res['response'];
        $decodded_response = json_decode(base64_decode($response));

        $payment_type = explode("-", $decodded_response->data->merchantTransactionId);
        auth()->login(User::findOrFail($payment_type[2]));
        // dd($payment_type[0], $payment_type[1], $request['merchantId'], $request['transactionId'], $request->all());

        if ($decodded_response->code  == 'PAYMENT_SUCCESS') {

            if ($payment_type[0] == 'cart_payment') {
                return (new CheckoutController)->checkout_done($payment_type[1], json_encode($decodded_response->data));
            } elseif ($payment_type[0] == 'order_re_payment') {
                $payment_data = array();
                $payment_data['order_id'] = $payment_type[1];
                $payment_data['payment_method'] = 'phonepe';
                return (new CheckoutController)->orderRePaymentDone($payment_data, json_encode($decodded_response->data));
            } elseif ($payment_type[0] == 'wallet_payment') {
                $payment_data = array();
                $payment_data['amount'] = $decodded_response->data->amount / 100;
                $payment_data['payment_method'] = 'phonepe';
                return (new WalletController)->wallet_payment_done($payment_data, json_encode($decodded_response->data));
            } elseif ($payment_type[0] == 'customer_package_payment') {
                $payment_data = array();
                $payment_data['customer_package_id'] = $payment_type[1];
                $payment_data['payment_method'] = 'phonepe';
                return (new CustomerPackageController)->purchase_payment_done($payment_data, json_encode($decodded_response->data));
            } elseif ($payment_type[0] == 'seller_package_payment') {
                $payment_data = array();
                $payment_data['seller_package_id'] = $payment_type[1];
                $payment_data['payment_method'] = 'phonepe';
                return (new SellerPackageController)->purchase_payment_done($payment_data, json_encode($decodded_response->data));
            }
        }
    }

    private function getPhonePeOrderStatus($moid, $accessToken)
    {
        $isSandbox = get_setting('phonepe_sandbox') == 1;
        $checkStatusUrl = $isSandbox
            ? "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/{$moid}/status"
            : "https://api.phonepe.com/apis/pg/checkout/v2/order/{$moid}/status";


        $url = $checkStatusUrl;
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'O-Bearer ' . $accessToken,
            ])->get($url);

            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
