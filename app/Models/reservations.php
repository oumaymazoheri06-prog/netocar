<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class reservations extends Model
{
    protected $fillable = ['client_id', 'service_id', 'employee_id', 'vehicle_type', 'plate_number', 'status', 'reservation_date', 'duration_minutes', 'agency_id', 'branch_id'];

    /** @use HasFactory<\Database\Factories\ReservationsFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'reservations';

    protected $casts = [
        'reservation_date' => 'datetime',
    ];

    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'reservation_id')->withTrashed();
    }

    public function agency()
    {
        return $this->belongsTo(agencies::class, 'agency_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id')->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo(clients::class)->withTrashed();

    }

    public function employee()
    {
        return $this->belongsTo(employees::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(services::class)->withTrashed();
    }
}
