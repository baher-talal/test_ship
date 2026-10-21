<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'shipment_id', 'kind', 'description', 'amount', 'shipping_amount'];

    protected $casts = ['amount' => 'float', 'shipping_amount' => 'float'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
