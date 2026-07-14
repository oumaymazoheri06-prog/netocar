<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class services extends Model
{
    /** @use HasFactory<\Database\Factories\ServicesFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'services';

    protected $fillable = ['agency_id', 'branch_id', 'name', 'description', 'price', 'duration_minutes', 'photo'];

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
        return $this->hasMany(reservations::class, 'service_id');
    }
}
