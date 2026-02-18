<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('category');
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->string('hsn_sac')->nullable();
            $table->string('unit')->default('pcs');
            $table->integer('warranty_months')->nullable();
            $table->boolean('track_serial')->default(false);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
