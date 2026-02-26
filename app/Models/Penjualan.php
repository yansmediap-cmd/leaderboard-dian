<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_id',
        'dealer_id',
        'no_spk',
        'tipe_motor',
        'tanggal_spk',
        'bulan',
        'tahun',
        'jumlah_unit',
        'harga_unit',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_spk' => 'date',
            'bulan' => 'integer',
            'tahun' => 'integer',
            'jumlah_unit' => 'integer',
            'harga_unit' => 'decimal:2',
            'total_harga' => 'decimal:2',
        ];
    }

    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}
