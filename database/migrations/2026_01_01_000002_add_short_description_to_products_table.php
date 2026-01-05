<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'short_description')) {
                $table->text('short_description')->nullable()->after('description');
            }
        });

        Schema::table('product_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('product_translations', 'short_description')) {
                $table->text('short_description')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'short_description')) {
                $table->dropColumn('short_description');
            }
        });

        Schema::table('product_translations', function (Blueprint $table) {
            if (Schema::hasColumn('product_translations', 'short_description')) {
                $table->dropColumn('short_description');
            }
        });
    }
};
