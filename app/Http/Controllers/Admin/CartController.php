<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CartReminderMail;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with([
            'carts' => function ($query) {
                $query->with([
                    'product.thumbnail',
                    'product.stocks.wholesalePrices',
                    'product.taxes',
                ])->orderByDesc('updated_at');
            }
        ])
            ->whereHas('carts', function ($query) {
                $query->whereNotNull('user_id');
            })
            ->orderBy('name')
            ->paginate(20);

        return view('backend.marketing.cart_list', compact('users'));
    }

    public function show(Cart $cart): JsonResponse
    {
        $user = $cart->user;
        abort_unless($user, 404);

        $cartItems = Cart::with([
            'product.thumbnail',
            'product.stocks.wholesalePrices',
            'product.taxes',
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get();

        $summary = $this->buildCartSummary($cartItems);

        return response()->json([
            'html' => view('backend.marketing.partials.cart_details', [
                'user' => $user,
                'cartItems' => $cartItems,
                'summary' => $summary,
                'latestCart' => $cartItems->first(),
            ])->render(),
        ]);
    }

    public function sendEmail(Cart $cart): JsonResponse
    {
        $user = $cart->user;
        if (!$user || empty($user->email)) {
            return response()->json(['message' => translate('No email address found for this user.')], 422);
        }

        $cartItems = Cart::with([
            'product.thumbnail',
            'product.stocks.wholesalePrices',
            'product.taxes',
        ])
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->get();

        $summary = $this->buildCartSummary($cartItems);
        $checkoutUrl = route('cart');

        Mail::to($user->email)->send(new CartReminderMail($user, $cartItems, $summary, $checkoutUrl));

        return response()->json(['message' => translate('Email reminder sent.')]);
    }

    public function sendSMS(Cart $cart): JsonResponse
    {
        $user = $cart->user;
        if (!$user || empty($user->phone)) {
            return response()->json(['message' => translate('No phone number found for this user.')], 422);
        }

        $checkoutUrl = route('cart');
        $message = "Hi {$user->name}, you have items waiting in your cart. Complete your order now: {$checkoutUrl}";

        Log::info('Cart SMS reminder', [
            'user_id' => $user->id,
            'phone' => $user->phone,
            'message' => $message,
        ]);

        // TODO: integrate SMS provider (Twilio / Fast2SMS / MSG91)

        return response()->json(['message' => translate('SMS reminder queued.')]);
    }

    private function buildCartSummary(Collection $cartItems): array
    {
        $subtotal = 0;
        $taxTotal = 0;
        $shipping = 0;
        $discount = 0;
        $couponCode = optional($cartItems->firstWhere('coupon_applied', 1))->coupon_code;

        foreach ($cartItems as $item) {
            if (!$item->relationLoaded('product') || !$item->product) {
                continue;
            }

            $product = $item->product;
            $unitPrice = cart_product_price($item, $product, false, false);

            $subtotal += $unitPrice * $item->quantity;
            $taxTotal += cart_product_tax($item, $product, false) * $item->quantity;
            $shipping += $item->shipping_cost ?? 0;
            $discount += $item->discount ?? 0;
        }

        $total = max(0, $subtotal + $taxTotal + $shipping - $discount);

        return [
            'subtotal' => $subtotal,
            'taxTotal' => $taxTotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'couponCode' => $couponCode,
            'total' => $total,
        ];
    }
}
