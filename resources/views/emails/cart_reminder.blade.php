<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ translate('Complete your order') }}</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 20px;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 6px 24px rgba(0,0,0,0.06);">
        <div style="background:#0d6efd; color:#fff; padding:18px 20px;">
            <h2 style="margin:0; font-weight:600;">{{ translate('You left something behind!') }}</h2>
        </div>
        <div style="padding:20px;">
            <p style="margin:0 0 12px;">{{ translate('Hi') }} {{ $user->name ?? translate('there') }},</p>
            <p style="margin:0 0 20px;">{{ translate('You still have items waiting in your cart. Checkout now to complete your order.') }}</p>

            <h4 style="margin:0 0 10px;">{{ translate('Top items in your cart') }}</h4>
            <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                @foreach($cartItems->take(3) as $item)
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:10px 0; color:#333;">
                            {{ optional($item->product)->getTranslation('name') ?? translate('Product') }}
                        </td>
                        <td style="padding:10px 0; text-align:center; color:#555;">
                            x{{ $item->quantity }}
                        </td>
                        <td style="padding:10px 0; text-align:right; color:#111; font-weight:600;">
                            {{ single_price($item->line_total ?? 0) }}
                        </td>
                    </tr>
                @endforeach
            </table>

            <div style="margin:20px 0;">
                <p style="margin:0 0 6px;"><strong>{{ translate('Subtotal') }}:</strong> {{ single_price($summary['subtotal'] ?? 0) }}</p>
                <p style="margin:0 0 6px;"><strong>{{ translate('Tax') }}:</strong> {{ single_price($summary['taxTotal'] ?? 0) }}</p>
                <p style="margin:0 0 6px;"><strong>{{ translate('Shipping') }}:</strong> {{ single_price($summary['shipping'] ?? 0) }}</p>
                @if(($summary['discount'] ?? 0) > 0)
                    <p style="margin:0 0 6px;"><strong>{{ translate('Discount') }}:</strong> -{{ single_price($summary['discount'] ?? 0) }}</p>
                @endif
                @if(!empty($summary['couponCode']))
                    <p style="margin:0 0 6px;"><strong>{{ translate('Coupon') }}:</strong> {{ $summary['couponCode'] }}</p>
                @endif
                <p style="margin:10px 0 0; font-size:18px; font-weight:700;">
                    {{ translate('Total') }}: {{ single_price($summary['total'] ?? 0) }}
                </p>
            </div>

            <div style="text-align:center; margin:24px 0;">
                <a href="{{ $checkoutUrl }}" style="background:#0d6efd; color:#fff; padding:12px 20px; text-decoration:none; border-radius:6px; display:inline-block; font-weight:600;">
                    {{ translate('Complete checkout') }}
                </a>
            </div>

            <p style="margin:0; color:#666;">{{ translate('Need help? Reply to this email and we will assist you right away.') }}</p>
        </div>
    </div>
</body>
</html>
