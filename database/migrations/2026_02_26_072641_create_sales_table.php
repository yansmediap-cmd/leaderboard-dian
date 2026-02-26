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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
            $table->string('kode_sales', 30)->unique();
            $table->string('nama_sales', 100);
            $table->string('foto_sales')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->integer('target_bulanan')->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();

            $table->index('dealer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
