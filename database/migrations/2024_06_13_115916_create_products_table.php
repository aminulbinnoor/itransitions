<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id'); // AUTO_INCREMENT primary key
            $table->string('name', 50); // Product name
            $table->string('description', 255); // Product description
            $table->string('product_code', 10)->unique(); // Unique product code
            $table->dateTime('added_date')->nullable(); // Added date, nullable
            $table->dateTime('discontinued_date')->nullable(); // Discontinued date, nullable
            $table->timestamp('timestamp')->useCurrent()->useCurrentOnUpdate(); // Timestamp, defaults to CURRENT_TIMESTAMP on update

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
