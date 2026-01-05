<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrepaidDiscountsTable extends Migration
{
    public function up()
    {
        Schema::create('prepaid_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('min_amount', 10, 2)->default(0);
            $table->decimal('max_amount', 10, 2)->nullable();
            $table->decimal('percent', 5, 2);
            $table->integer('priority')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('prepaid_discounts');
    }
}
