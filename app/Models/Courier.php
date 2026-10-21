<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use BranchScoped;

    const STATUSES = ['available' => 'Available', 'busy' => 'Busy', 'off' => 'Out of service'];

    const VEHICLES = ['motorcycle' => 'Motorcycle', 'car' => 'Car', 'van' => 'Van', 'truck' => 'Truck'];

    protected $fillable = [
        'branch_id', 'user_id', 'name', 'phone', 'national_id', 'vehicle_type', 'vehicle_number', 'status', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function statusLabel()
    {
        return __(self::STATUSES[$this->status] ?? $this->status);
    }
}
