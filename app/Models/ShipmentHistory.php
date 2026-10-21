<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentHistory extends Model
{
    protected $fillable = ['shipment_id', 'user_id', 'status', 'location', 'note'];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel()
    {
        return __(Shipment::STATUSES[$this->status]['label'] ?? $this->status);
    }

    public function statusColor()
    {
        return Shipment::STATUSES[$this->status]['color'] ?? 'secondary';
    }
}
