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
        Schema::create('leaderboard_items', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_faktur')->nullable()->index();
            $table->string('dealer', 150)->index();
            $table->string('tipe_unit', 150)->nullable()->index();
            $table->string('tipe_beli', 50)->nullable()->index();
            $table->string('jabatan', 100)->nullable();
            $table->string('nama_sales', 100)->index();
            $table->string('foto_profile')->nullable();
            $table->string('source_file')->nullable();
            $table->timestamps();

            $table->index(['dealer', 'nama_sales']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboard_items');
    }
};
