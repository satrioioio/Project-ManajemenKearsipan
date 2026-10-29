<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_LEGAL = 'legal';

    public const ROLE_PM = 'pm';

    public const ROLE_STAFF = 'staff';

    public const ROLES = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_LEGAL => 'Legal',
        self::ROLE_PM => 'Project Manager',
        self::ROLE_STAFF => 'Staf',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isLegal(): bool
    {
        return $this->role === self::ROLE_LEGAL;
    }

    public function isPm(): bool
    {
        return $this->role === self::ROLE_PM;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function canManageDocuments(): bool
    {
        return $this->isAdmin() || $this->isLegal() || $this->isPm();
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }
}
