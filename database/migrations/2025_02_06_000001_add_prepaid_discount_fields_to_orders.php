<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrepaidDiscountFieldsToOrders extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('prepaid_discount_rule_id')->nullable()->after('discount');
            $table->decimal('prepaid_discount_percent', 5, 2)->nullable()->after('prepaid_discount_rule_id');
            $table->decimal('prepaid_discount_amount', 10, 2)->default(0)->after('prepaid_discount_percent');
            if (!Schema::hasColumn('orders', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('prepaid_discount_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'prepaid_discount_rule_id',
                'prepaid_discount_percent',
                'prepaid_discount_amount',
            ]);
            if (Schema::hasColumn('orders', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });
    }
}
