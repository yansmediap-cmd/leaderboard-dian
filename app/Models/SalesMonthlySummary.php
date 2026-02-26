<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesMonthlySummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_id',
        'dealer_id',
        'bulan',
        'tahun',
        'total_spk',
        'total_do',
        'total_revenue',
    ];

    protected function casts(): array
    {
        return [
            'bulan' => 'integer',
            'tahun' => 'integer',
            'total_spk' => 'integer',
            'total_do' => 'integer',
            'total_revenue' => 'decimal:2',
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
