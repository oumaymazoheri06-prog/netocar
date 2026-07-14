<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'name',
        'code',
        'address',
        'phone_number',
        'opening_time',
        'closing_time',
        'simultaneous_capacity',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'simultaneous_capacity' => 'integer',
    ];

    public function agency()
    {
        return $this->belongsTo(agencies::class, 'agency_id');
    }

    public function clients()
    {
        return $this->hasMany(clients::class, 'branch_id');
    }

    public function employees()
    {
        return $this->hasMany(employees::class, 'branch_id');
    }

    public function services()
    {
        return $this->hasMany(services::class, 'branch_id');
    }

    public function reservations()
    {
        return $this->hasMany(reservations::class, 'branch_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'branch_id');
    }
}
