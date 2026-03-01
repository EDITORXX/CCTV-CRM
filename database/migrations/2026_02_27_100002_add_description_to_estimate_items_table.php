<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->string('description')->nullable()->after('estimate_id');
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE estimate_items MODIFY product_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support MODIFY; would need recreate - skip making nullable for sqlite if problematic
        }

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE estimate_items MODIFY product_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropColumn('description');
        });
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }
};
