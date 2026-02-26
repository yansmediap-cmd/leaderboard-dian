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
        Schema::create('sales_monthly_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->year('tahun');
            $table->unsignedInteger('total_spk')->default(0);
            $table->unsignedInteger('total_do')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['sales_id', 'bulan', 'tahun']);
            $table->index(['dealer_id', 'bulan', 'tahun']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_monthly_summaries');
    }
};
