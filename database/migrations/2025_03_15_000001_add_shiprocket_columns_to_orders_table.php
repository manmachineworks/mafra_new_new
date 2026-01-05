<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shiprocket_order_id')->nullable()->index();
            $table->string('shiprocket_shipment_id')->nullable()->index();
            $table->string('shiprocket_awb')->nullable()->index();
            $table->string('shiprocket_status')->nullable();
            $table->string('shiprocket_courier_name')->nullable();
            $table->string('shiprocket_label_url')->nullable();
            $table->string('shiprocket_manifest_url')->nullable();
            $table->timestamp('shiprocket_pickup_scheduled_at')->nullable();
            $table->timestamp('shiprocket_last_synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shiprocket_order_id',
                'shiprocket_shipment_id',
                'shiprocket_awb',
                'shiprocket_status',
                'shiprocket_courier_name',
                'shiprocket_label_url',
                'shiprocket_manifest_url',
                'shiprocket_pickup_scheduled_at',
                'shiprocket_last_synced_at',
            ]);
        });
    }
};
