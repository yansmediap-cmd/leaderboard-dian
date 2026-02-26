<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dealer_id',
        'method',
        'path',
        'status_code',
        'ip_address',
        'user_agent',
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}
