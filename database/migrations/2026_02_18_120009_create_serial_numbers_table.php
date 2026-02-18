<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->onDelete('cascade');
            $table->string('serial_number');
            $table->string('status')->default('in_stock');
            $table->unsignedBigInteger('invoice_item_id')->nullable();
            $table->foreignId('installed_site_id')->nullable()->constrained('sites')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('serial_number');
            $table->index(['company_id', 'serial_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_numbers');
    }
};
