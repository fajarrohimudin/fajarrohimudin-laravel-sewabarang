<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom qty & status ke tabel products
        Schema::table('products', function (Blueprint $table) {
            $table->integer('qty')->default(0)->after('price');
            $table->enum('status', ['tersedia', 'disewakan', 'maintenance'])
                  ->default('tersedia')
                  ->after('qty');
        });

        // Tambah kolom status ke tabel transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', ['proses', 'selesai', 'dibatalkan'])
                  ->default('proses')
                  ->after('is_paid');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['qty', 'status']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};