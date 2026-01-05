<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('current_status')->nullable()->after('delivery_status');
            $table->string('status_code')->nullable()->after('current_status');
            $table->timestamp('status_updated_at')->nullable()->after('status_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['current_status', 'status_code', 'status_updated_at']);
        });
    }
};
