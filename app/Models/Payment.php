<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'agency_id',
        'plan',
        'billing_period',
        'period_starts_at',
        'period_ends_at',
        'amount',
        'status',
        'processed_at',
        'reviewed_by',
        'payment_method',
        'receipt_photo',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'period_starts_at' => 'date',
        'period_ends_at' => 'date',
        'processed_at' => 'datetime',
    ];

    public function agency()
    {
        return $this->belongsTo(agencies::class);
    }
}
