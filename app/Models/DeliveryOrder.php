<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_id',
        'dealer_id',
        'no_do',
        'tanggal_do',
        'jumlah_unit_do',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_do' => 'date',
            'jumlah_unit_do' => 'integer',
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
