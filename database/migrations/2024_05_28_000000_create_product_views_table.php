<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('ip_address', 64);
            $table->date('view_date');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'ip_address', 'view_date'], 'product_view_unique_per_day');
            $table->index(['product_id', 'last_seen_at'], 'product_view_live_index');
            $table->index(['product_id', 'view_date'], 'product_view_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_views');
    }
};
