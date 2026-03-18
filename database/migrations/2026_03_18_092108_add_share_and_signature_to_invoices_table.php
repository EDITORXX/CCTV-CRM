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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('share_token', 64)->nullable()->unique()->after('notes');
            $table->text('customer_signature')->nullable()->after('share_token');
            $table->string('customer_ip', 64)->nullable()->after('customer_signature');
            $table->timestamp('customer_signed_at')->nullable()->after('customer_ip');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['share_token', 'customer_signature', 'customer_ip', 'customer_signed_at']);
        });
    }
};
