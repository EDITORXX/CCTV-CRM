<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->enum('type', ['faq', 'guide'])->default('faq');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'type', 'is_published']);
            $table->index(['company_id', 'brand']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_articles');
    }
};
