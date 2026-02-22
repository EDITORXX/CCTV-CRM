<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_advance_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_advance_id')->constrained('customer_advances')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['customer_advance_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_advance_allocations');
    }
};
