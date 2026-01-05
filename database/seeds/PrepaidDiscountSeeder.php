<?php

use Illuminate\Database\Seeder;
use App\Models\PrepaidDiscount;

class PrepaidDiscountSeeder extends Seeder
{
    public function run()
    {
        $rules = [
            [
                'title' => '5% on ₹500-₹999',
                'min_amount' => 500,
                'max_amount' => 999,
                'percent' => 5,
                'priority' => 1,
            ],
            [
                'title' => '7% on ₹1000-₹4999',
                'min_amount' => 1000,
                'max_amount' => 4999,
                'percent' => 7,
                'priority' => 2,
            ],
            [
                'title' => '10% on ₹5000+',
                'min_amount' => 5000,
                'max_amount' => null,
                'percent' => 10,
                'priority' => 3,
            ],
        ];

        foreach ($rules as $rule) {
            PrepaidDiscount::updateOrCreate(
                ['title' => $rule['title']],
                $rule + ['is_active' => true]
            );
        }
    }
}
