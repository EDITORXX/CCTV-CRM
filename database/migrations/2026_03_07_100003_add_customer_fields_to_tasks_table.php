<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('task_category_id')->constrained('customers')->nullOnDelete();
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('customer_phone')->nullable()->after('customer_name');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'customer_name', 'customer_phone']);
        });
    }
};
