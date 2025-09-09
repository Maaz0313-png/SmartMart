<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRequest extends Model
{
    use HasFactory;
    const TYPE_EXPORT = 'export';
    const TYPE_DELETE = 'delete';
    const TYPE_RECTIFICATION = 'rectification';
    const TYPE_PORTABILITY = 'portability';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'type',
        'status',
        'reason',
        'admin_notes',
        'requested_at',
        'processed_at',
        'completed_at',
        'file_path',
        'export_file_path',
        'expires_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}