<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    public const JENIS = [
        'kontrak' => 'Kontrak Utama',
        'addendum' => 'Addendum',
        'bast' => 'Berita Acara Serah Terima',
        'izin' => 'Dokumen Perizinan',
        'lainnya' => 'Dokumen Lainnya',
    ];

    public const SECURITY_LEVELS = [
        'biasa' => 'Biasa / Terbuka',
        'terbatas' => 'Terbatas',
        'rahasia' => 'Rahasia',
        'sangat_rahasia' => 'Sangat Rahasia',
    ];

    public const LIFECYCLE_STATUSES = [
        'active' => 'Aktif',
        'inactive' => 'Inaktif',
        'pending_destruction' => 'Menunggu Pemusnahan',
        'permanent' => 'Arsip Permanen',
        'destroyed' => 'Dimusnahkan',
    ];

    public const RETENSI_MINIMAL_TAHUN = 10;

    protected $fillable = [
        'project_id',
        'project_phase_id',
        'classification_id',
        'parent_document_id',
        'nomor_dokumen',
        'judul',
        'jenis',
        'versi',
        'deskripsi',
        'tanggal_dokumen',
        'valid_from',
        'valid_until',
        'retention_date',
        'lifecycle_status',
        'security_level',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'checksum_sha256',
        'is_locked',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_dokumen' => 'date',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'retention_date' => 'date',
            'is_locked' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Document $document) {
            if (! $document->tanggal_dokumen) {
                return;
            }

            $computed = $document->computeRetentionDate();

            if (! $computed) {
                return;
            }

            $isContract = in_array($document->jenis, ['kontrak', 'addendum'], true);

            if (blank($document->retention_date) || ($isContract && $document->retention_date->lt($computed))) {
                $document->retention_date = $computed;
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'project_phase_id');
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_document_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_document_id')->orderBy('valid_from');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class)->latest('created_at');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term) {
            $query->where('judul', 'like', "%{$term}%")
                ->orWhere('nomor_dokumen', 'like', "%{$term}%")
                ->orWhere('deskripsi', 'like', "%{$term}%");
        });
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin() || $user->isLegal()) {
            return $query;
        }

        $query->where('security_level', '!=', 'sangat_rahasia');

        if ($user->isPm()) {
            return $query;
        }

        return $query
            ->where('security_level', 'biasa')
            ->whereNotIn('jenis', ['kontrak', 'addendum']);
    }

    public function isAccessibleBy(User $user): bool
    {
        if ($user->isAdmin() || $user->isLegal()) {
            return true;
        }

        if ($this->security_level === 'sangat_rahasia') {
            return false;
        }

        if ($user->isPm()) {
            return true;
        }

        return $this->security_level === 'biasa'
            && ! in_array($this->jenis, ['kontrak', 'addendum'], true);
    }

    public function isDownloadableBy(User $user): bool
    {
        return $this->isAccessibleBy($user) && filled($this->file_path);
    }

    public function computeRetentionDate(): ?Carbon
    {
        if (! $this->tanggal_dokumen) {
            return null;
        }

        $tahun = $this->classification?->retensi_tahun ?? self::RETENSI_MINIMAL_TAHUN;

        if (in_array($this->jenis, ['kontrak', 'addendum'], true)) {
            $tahun = max($tahun, self::RETENSI_MINIMAL_TAHUN);
        }

        return $this->tanggal_dokumen->copy()->addYears($tahun);
    }

    public function daysUntilRetention(): ?int
    {
        if (! $this->retention_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->retention_date->startOfDay(), false);
    }

    public function jenisLabel(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst($this->jenis);
    }

    public function securityLabel(): string
    {
        return self::SECURITY_LEVELS[$this->security_level] ?? ucfirst($this->security_level);
    }

    public function lifecycleLabel(): string
    {
        return self::LIFECYCLE_STATUSES[$this->lifecycle_status] ?? ucfirst($this->lifecycle_status);
    }

    public function securityColor(): string
    {
        return match ($this->security_level) {
            'sangat_rahasia' => 'danger',
            'rahasia' => 'warning',
            'terbatas' => 'info',
            default => 'secondary',
        };
    }

    public function jenisColor(): string
    {
        return match ($this->jenis) {
            'kontrak' => 'primary',
            'addendum' => 'warning',
            'bast' => 'success',
            'izin' => 'info',
            default => 'secondary',
        };
    }

    public function isValidOn(?Carbon $date = null): bool
    {
        $date = ($date ?? now())->startOfDay();

        if ($this->valid_from->gt($date)) {
            return false;
        }

        return $this->valid_until === null || $this->valid_until->gte($date);
    }

    public function fileSizeLabel(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return number_format($size, $unit === 0 ? 0 : 2, ',', '.').' '.$units[$unit];
    }
}
