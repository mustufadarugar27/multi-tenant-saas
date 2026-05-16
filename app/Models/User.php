<?php


namespace App\Models;

use App\Support\Concerns\HasUuid;
use App\Support\EnumConfig;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'email_verified_at',
        'failed_login_count',
        'locked_until',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'is_active' => 'boolean',
            'failed_login_count' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isCompanyAdmin(): bool
    {
        return $this->role === 'company_admin';
    }

    public function canManageTenant(): bool
    {
        return EnumConfig::userRoleCanManageTenant($this->role);
    }

    public function canManageProjects(): bool
    {
        return EnumConfig::userRoleCanManageProjects($this->role);
    }

    public function canManageUsers(): bool
    {
        return EnumConfig::userRoleCanManageUsers($this->role);
    }

    public function guardName(): string
    {
        return 'sanctum';
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function incrementFailedLogins(): void
    {
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $this->id)
            ->increment('failed_login_count');

        $this->refresh();

        if ($this->failed_login_count >= 5) {
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $this->id)
                ->update(['locked_until' => now()->addMinutes(15)->toDateTimeString()]);

            $this->refresh();
        }
    }

    public function clearFailedLogins(): void
    {
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $this->id)
            ->update([
                'failed_login_count' => 0,
                'locked_until' => null,
            ]);

        $this->refresh();
    }
}
