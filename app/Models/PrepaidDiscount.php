<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrepaidDiscount extends Model
{
    protected $fillable = [
        'title',
        'min_amount',
        'max_amount',
        'percent',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
