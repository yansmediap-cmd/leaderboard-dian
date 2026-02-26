<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderboardItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal_faktur',
        'dealer',
        'tipe_unit',
        'tipe_beli',
        'jabatan',
        'nama_sales',
        'foto_profile',
        'source_file',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_faktur' => 'date',
        ];
    }
}
