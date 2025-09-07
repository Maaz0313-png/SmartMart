<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataProcessingAgreement extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'version',
        'agreed_at',
        'ip_address',
        'user_agent',
        'is_active',
        'data_types',
        'processing_purposes',
        'retention_period',
    ];

    protected function casts(): array
    {
        return [
            'agreed_at' => 'datetime',
            'is_active' => 'boolean',
            'data_types' => 'array',
            'processing_purposes' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}