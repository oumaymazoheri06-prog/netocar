<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class clients extends Model
{
    /** @use HasFactory<\Database\Factories\ClientsFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = ['agency_id', 'branch_id', 'name', 'email', 'phone_number', 'normalized_phone'];

    protected static function booted(): void
    {
        static::saving(function (clients $client) {
            $client->normalized_phone = self::normalizePhone($client->phone_number);
        });
    }

    public static function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '') ?? '';

        if (str_starts_with($digits, '00212')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '212'.substr($digits, 1);
        }

        return $digits;
    }

    public function agency()
    {
        return $this->belongsTo(agencies::class, 'agency_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')->withTrashed();
    }

    public function reservation()
    {
        return $this->hasMany(reservations::class, 'client_id');
    }

    public function getPhoneAttribute()
    {
        return $this->phone_number;
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone_number'] = $value;
    }
}
