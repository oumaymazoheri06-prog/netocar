<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class agencies extends Model
{
    use HasFactory, SoftDeletes;

    public function getPlanAmountAttribute()
    {
        return (int) data_get(config('netocar.plans'), "{$this->package}.price_yearly_mad", 0);
    }

    public function getPlanNameAttribute()
    {
        return data_get(config('netocar.plans'), "{$this->package}.label", ucfirst((string) $this->package));
    }

    public function getPlanLimitsAttribute()
    {
        return data_get(config('netocar.plans'), "{$this->package}.limits", []);
    }

    public function getLicenseStatusNameAttribute(): string
    {
        return match ($this->license_status) {
            'active' => 'Active',
            'suspended' => 'Suspendue',
            default => 'Non configurée',
        };
    }

    public function getLicenseStateNameAttribute(): string
    {
        if ($this->license_status === 'suspended') {
            return 'Suspendue';
        }

        if ($this->license_expires_at && $this->license_expires_at->copy()->endOfDay()->isPast()) {
            return 'Expirée';
        }

        return 'Active';
    }

    public function hasActiveLicense(): bool
    {
        if ($this->license_status !== 'active') {
            return false;
        }

        if (! $this->license_expires_at) {
            return true;
        }

        return $this->license_expires_at->copy()->endOfDay()->isFuture();
    }

    public function getLicenseBlockReasonAttribute(): string
    {
        if ($this->license_status === 'suspended') {
            return 'Votre agence est suspendue. Contactez l’administrateur ou envoyez une demande de renouvellement.';
        }

        if ($this->license_expires_at && $this->license_expires_at->copy()->endOfDay()->isPast()) {
            return 'Votre abonnement a expiré. Renouvelez la cotisation annuelle pour réactiver l’accès.';
        }

        return 'Votre licence n’est pas active.';
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'agency_id');
    }

    public function branches()
    {
        return $this->hasMany(Branch::class, 'agency_id');
    }

    protected $table = 'agencies'; // optional if table name is plural

    protected $primaryKey = 'id'; // make sure it's 'id', not 'agency_id'

    protected $fillable = [
        'name',
        'email',
        'address',
        'phone_number',
        'package',
        'license_status',
        'license_expires_at',
        'activated_at',
    ];

    protected $casts = [
        'license_expires_at' => 'date',
        'activated_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return \Database\Factories\AgencyFactory::new();
    }

    public function user()
    {
        return $this->hasOne(User::class, 'agency_id')->where('role', UserRole::Manager->value);

    }

    public function employee()
    {
        return $this->hasMany(employees::class, 'agency_id');
    }

    public function client()
    {
        return $this->hasMany(clients::class, 'agency_id');
    }

    public function services()
    {
        return $this->hasMany(services::class, 'agency_id');
    }

    public function reservation()
    {
        return $this->hasMany(reservations::class, 'agency_id');
    }
}
