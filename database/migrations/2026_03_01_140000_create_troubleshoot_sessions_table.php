<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('troubleshoot_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('short_code', 16)->unique();
            $table->string('password');
            $table->enum('status', ['waiting', 'active', 'ended'])->default('waiting');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('troubleshoot_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('troubleshoot_session_id')->constrained()->onDelete('cascade');
            $table->string('from_peer');
            $table->string('to_peer');
            $table->string('type');
            $table->longText('payload');
            $table->boolean('consumed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('troubleshoot_signals');
        Schema::dropIfExists('troubleshoot_sessions');
    }
};
