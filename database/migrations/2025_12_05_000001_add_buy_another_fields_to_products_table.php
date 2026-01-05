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
            if (!Schema::hasColumn('products', 'buy_another_url')) {
                $table->string('buy_another_url')->nullable()->after('external_link_btn');
            }

            if (!Schema::hasColumn('products', 'buy_another_btn')) {
                $table->string('buy_another_btn')->nullable()->after('buy_another_url');
            }

            if (!Schema::hasColumn('products', 'buy_another_links')) {
                $table->longText('buy_another_links')->nullable()->after('buy_another_btn');
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
            if (Schema::hasColumn('products', 'buy_another_btn')) {
                $table->dropColumn('buy_another_btn');
            }

            if (Schema::hasColumn('products', 'buy_another_url')) {
                $table->dropColumn('buy_another_url');
            }

            if (Schema::hasColumn('products', 'buy_another_links')) {
                $table->dropColumn('buy_another_links');
            }
        });
    }
};
