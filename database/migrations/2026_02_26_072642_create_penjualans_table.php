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
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('dealer_id')->constrained('dealers')->cascadeOnDelete();
            $table->string('no_spk', 50)->unique();
            $table->string('tipe_motor', 100);
            $table->date('tanggal_spk');
            $table->unsignedTinyInteger('bulan');
            $table->year('tahun');
            $table->unsignedInteger('jumlah_unit');
            $table->decimal('harga_unit', 15, 2);
            $table->decimal('total_harga', 15, 2)->storedAs('jumlah_unit * harga_unit');
            $table->timestamps();

            $table->index('sales_id');
            $table->index(['bulan', 'tahun']);
            $table->index('tanggal_spk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
