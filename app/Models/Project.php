<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUSES = [
        'perencanaan' => 'Perencanaan',
        'berjalan' => 'Berjalan',
        'selesai' => 'Selesai',
        'sengketa' => 'Sengketa',
    ];

    protected $fillable = [
        'kode_proyek',
        'nama_proyek',
        'pemilik_pekerjaan',
        'lokasi',
        'nilai_kontrak',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'deskripsi',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'nilai_kontrak' => 'decimal:2',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('urutan');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term) {
            $query->where('nama_proyek', 'like', "%{$term}%")
                ->orWhere('kode_proyek', 'like', "%{$term}%")
                ->orWhere('pemilik_pekerjaan', 'like', "%{$term}%");
        });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function progressPercent(): int
    {
        $total = $this->phases->count();

        if ($total === 0) {
            return 0;
        }

        $done = $this->phases->where('status', 'selesai')->count();

        return (int) round($done / $total * 100);
    }
}
