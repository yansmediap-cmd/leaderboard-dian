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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
            $table->string('no_do', 50)->unique();
            $table->date('tanggal_do');
            $table->unsignedInteger('jumlah_unit_do');
            $table->timestamps();

            $table->index('sales_id');
            $table->index('tanggal_do');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
