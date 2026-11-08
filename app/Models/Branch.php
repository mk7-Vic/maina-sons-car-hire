<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'location',
        'phone',
        'email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function pickupBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'pickup_branch_id');
    }

    public function returnBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'return_branch_id');
    }
}
