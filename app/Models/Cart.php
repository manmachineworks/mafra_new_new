<?php

namespace App\Models;

use App\Models\Product;
use App\Models\User;
use App\Models\Address;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{

    protected $guarded = [];
    protected $fillable = ['address_id','price','tax','shipping_cost','discount','product_referral_code','coupon_code','coupon_applied','quantity','user_id','temp_user_id','owner_id','product_id','variation'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->updated_at && $this->updated_at->gt(now()->subMinutes(30))
            ? 'active'
            : 'abandoned';
    }

    public function getLineTotalAttribute(): float
    {
        if (!$this->relationLoaded('product') || !$this->product) {
            return 0;
        }

        $unitPrice = cart_product_price($this, $this->product, false, true);
        $discount = $this->discount ?? 0;
        $shipping = $this->shipping_cost ?? 0;

        return max(0, ($unitPrice * $this->quantity) + $shipping - $discount);
    }
}
