<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class employees extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeesFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'branch_id',
        'user_id',
        'name',
        'email',
        'phone_number',
        'job_title',
        'salary',
    ];

    public function agency()
    {

        return $this->belongsTo(agencies::class, 'agency_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservation()
    {
        return $this->hasMany(reservations::class, 'employee_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'employee_id');
    }
}
