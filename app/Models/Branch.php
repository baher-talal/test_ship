<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'city', 'address', 'phone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function couriers()
    {
        return $this->hasMany(Courier::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
