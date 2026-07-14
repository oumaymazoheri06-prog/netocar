<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_id',
        'agency_id',
        'branch_id',
        'reservation_id',
        'client_id',
        'employee_id',
        'vehicle_type',
        'plate_number',
        'status',
        'started_at',
        'completed_at',
        'price',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(reservations::class, 'reservation_id')->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(services::class)->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo(clients::class)->withTrashed();
    }

    public function agency()
    {
        return $this->belongsTo(agencies::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')->withTrashed();
    }

    public function employee()
    {
        return $this->belongsTo(employees::class)->withTrashed();
    }
}
