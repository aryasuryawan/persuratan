<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'uuid', 'role_id', 'direktorat_id', 'divisi_id', 'departemen_id', 'name', 'title', 'jabatan', 'email', 'password',
        'google_id', 'avatar_url', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function direktorat(): BelongsTo
    {
        return $this->belongsTo(Direktorat::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function departemen(): BelongsTo
    {
        return $this->belongsTo(Departemen::class);
    }

    public function pimpinanYangDilayani(): HasMany
    {
        return $this->hasMany(SekretarisPimpinan::class, 'sekretaris_id');
    }

    public function sekretarisSaya(): HasMany
    {
        return $this->hasMany(SekretarisPimpinan::class, 'pimpinan_id');
    }

    public function hasRole(string $kode): bool
    {
        return $this->role?->kode === $kode;
    }

    public function hasPermission(string $permissionKode): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return $this->role?->hasPermission($permissionKode) ?? false;
    }
}
