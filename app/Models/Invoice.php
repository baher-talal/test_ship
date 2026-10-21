<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use BranchScoped;

    const STATUSES = [
        'unpaid' => ['label' => 'Unpaid', 'color' => 'warning'],
        'paid' => ['label' => 'Paid', 'color' => 'success'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger'],
    ];

    protected $fillable = [
        'invoice_number', 'branch_id', 'customer_id', 'created_by', 'issue_date', 'due_date',
        'subtotal', 'discount', 'tax_rate', 'tax_amount', 'total', 'status', 'paid_at', 'notes',
    ];

    protected $dates = ['issue_date', 'due_date', 'paid_at'];

    protected $casts = [
        'subtotal' => 'float', 'discount' => 'float', 'tax_rate' => 'float',
        'tax_amount' => 'float', 'total' => 'float',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    /** Sum of the item amounts (goods price and other lines), without shipping */
    public function itemsTotal()
    {
        return (float) $this->items->sum('amount');
    }

    /** Sum of the shipping fees of all shipments on the invoice (shown before the total due) */
    public function shippingTotal()
    {
        return (float) $this->items->sum('shipping_amount');
    }

    public function statusLabel()
    {
        return __(self::STATUSES[$this->status]['label'] ?? $this->status);
    }

    public function statusColor()
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public static function generateNumber()
    {
        $next = (int) static::max('id') + 1;

        do {
            $number = 'INV-'.date('Y').'-'.str_pad($next, 5, '0', STR_PAD_LEFT);
            $next++;
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }
}
