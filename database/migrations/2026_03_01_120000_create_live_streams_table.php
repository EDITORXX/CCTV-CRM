<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_streams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 64)->unique();
            $table->string('password')->nullable();
            $table->string('title')->nullable();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('live_stream_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_stream_id')->constrained()->onDelete('cascade');
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
        Schema::dropIfExists('live_stream_signals');
        Schema::dropIfExists('live_streams');
    }
};
