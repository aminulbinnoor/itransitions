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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->after('discontinued_date'); // Adding stock column after dtmDiscontinued
            $table->decimal('price', 8, 2)->after('stock'); // Adding price column after stock
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock'); // Dropping stock column
            $table->dropColumn('price'); // Dropping price column
        });
    }
};
