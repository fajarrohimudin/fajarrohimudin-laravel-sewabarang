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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->default('pcs');
            $table->text('description')->nullable();
            $table->integer('qty_total')->default(0);
            $table->integer('qty_available')->default(0);
            $table->integer('qty_damaged')->default(0);
            $table->enum('status', ['tersedia', 'rusak', 'hilang'])->default('tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
