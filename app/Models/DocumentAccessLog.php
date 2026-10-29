<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessLog extends Model
{
    public const UPDATED_AT = null;

    public const ACTIONS = [
        'view' => 'Melihat',
        'download' => 'Mengunduh',
        'create' => 'Membuat',
        'update' => 'Mengubah',
        'delete' => 'Menghapus',
        'lock' => 'Mengunci',
        'unlock' => 'Membuka Kunci',
        'denied' => 'Akses Ditolak',
    ];

    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? ucfirst($this->action);
    }
}
