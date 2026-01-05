<?php

namespace App\Services;

use App\Models\PrepaidDiscount;

class PrepaidDiscountService
{
    public function getApplicableRule(float $subtotal): ?PrepaidDiscount
    {
        return PrepaidDiscount::query()
            ->where('is_active', true)
            ->where('min_amount', '<=', $subtotal)
            ->where(function ($q) use ($subtotal) {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $subtotal);
            })
            ->orderBy('priority')
            ->orderByDesc('percent')
            ->first();
    }

    public function calculateDiscount(float $subtotal, PrepaidDiscount $rule): float
    {
        return round(($subtotal * $rule->percent) / 100, 2);
    }

    public function applyToTotals(float $subtotal, float $shipping, float $tax, string $paymentType): array
    {
        $isCod = in_array(strtolower($paymentType), ['cod', 'cash_on_delivery', 'cashondelivery']);
        if ($isCod) {
            return $this->formatResult($subtotal, $shipping, $tax, null, 0);
        }

        $rule = $this->getApplicableRule($subtotal);
        if (!$rule) {
            return $this->formatResult($subtotal, $shipping, $tax, null, 0);
        }

        $discountAmount = $this->calculateDiscount($subtotal, $rule);

        return $this->formatResult($subtotal, $shipping, $tax, $rule, $discountAmount);
    }

    private function formatResult(float $subtotal, float $shipping, float $tax, ?PrepaidDiscount $rule, float $discount): array
    {
        return [
            'discount_amount' => $discount,
            'discount_percent' => $rule?->percent,
            'discount_rule_id' => $rule?->id,
            'grand_total' => max(0, $subtotal - $discount + $shipping + $tax),
            'rule' => $rule,
        ];
    }
}
