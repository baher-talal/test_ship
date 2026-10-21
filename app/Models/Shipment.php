<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Shipment extends Model
{
    use BranchScoped {
        scopeForUser as protected scopeForUserByBranch;
    }

    const STATUSES = [
        'pending' => ['label' => 'Pending', 'color' => 'secondary', 'icon' => 'bi-hourglass-split'],
        'picked_up' => ['label' => 'Picked up from customer', 'color' => 'info', 'icon' => 'bi-box-seam'],
        'in_transit' => ['label' => 'In transit', 'color' => 'primary', 'icon' => 'bi-truck'],
        'out_for_delivery' => ['label' => 'Out for delivery', 'color' => 'warning', 'icon' => 'bi-bicycle'],
        'delivered' => ['label' => 'Delivered', 'color' => 'success', 'icon' => 'bi-check-circle'],
        'returned' => ['label' => 'Returned', 'color' => 'dark', 'icon' => 'bi-arrow-return-left'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'danger', 'icon' => 'bi-x-circle'],
    ];

    const FINAL_STATUSES = ['delivered', 'returned', 'cancelled'];

    const SERVICES = ['standard' => 'Standard', 'express' => 'Express', 'same_day' => 'Same day'];

    // The payment method decides whether the goods price is collected from the receiver on delivery
    const PAYMENTS = [
        'prepaid' => 'Do not collect the goods price',
        'cod' => 'Collect the goods price from the receiver (COD)',
    ];

    const PAYERS = ['sender' => 'Paid by the sender', 'receiver' => 'Paid by the receiver (collected with the goods)'];

    const PICKUP_TYPES = ['pickup' => 'Pickup from the sender address', 'drop_off' => 'Sender drops off at the branch'];

    const PACKAGE_TYPES = [
        'parcel' => 'Parcel', 'document' => 'Documents', 'clothes' => 'Clothes', 'electronics' => 'Electronics',
        'food' => 'Food', 'cosmetics' => 'Cosmetics', 'other' => 'Other',
    ];

    protected $fillable = [
        'tracking_number', 'reference_number', 'pickup_type', 'receiver_phone2', 'receiver_district', 'receiver_landmark',
        'package_type', 'length', 'width', 'height', 'is_fragile', 'allow_open', 'goods_value', 'total_amount',
        'shipping_payer', 'branch_id', 'customer_id', 'courier_id', 'created_by', 'invoice_id',
        'origin_city', 'receiver_name', 'receiver_phone', 'receiver_city', 'receiver_address',
        'service_type', 'weight', 'pieces', 'description', 'shipping_cost', 'payment_method',
        'cod_amount', 'status', 'expected_delivery', 'delivered_at', 'notes',
    ];

    protected $dates = ['expected_delivery', 'delivered_at'];

    protected $casts = [
        'weight' => 'float',
        'shipping_cost' => 'float',
        'cod_amount' => 'float',
        'goods_value' => 'float',
        'total_amount' => 'float',
        'length' => 'float',
        'width' => 'float',
        'height' => 'float',
        'is_fragile' => 'boolean',
        'allow_open' => 'boolean',
    ];

    /* ---------- Relations ---------- */

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function histories()
    {
        return $this->hasMany(ShipmentHistory::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    /* ---------- Scopes ---------- */

    /**
     * General manager: all shipments, courier: own shipments only, others: branch shipments.
     */
    public function scopeForUser($query, $user)
    {
        if ($user->isCourier()) {
            $courierId = optional($user->courier)->id;

            return $query->where('shipments.courier_id', $courierId ?: 0);
        }

        return $this->scopeForUserByBranch($query, $user);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('shipments.status', self::FINAL_STATUSES);
    }

    public function scopeOverdue($query)
    {
        return $query->active()
            ->whereNotNull('shipments.expected_delivery')
            ->whereDate('shipments.expected_delivery', '<', Carbon::today());
    }

    /* ---------- Display helpers ---------- */

    public function statusLabel()
    {
        return __(self::STATUSES[$this->status]['label'] ?? $this->status);
    }

    public function statusColor()
    {
        return self::STATUSES[$this->status]['color'] ?? 'secondary';
    }

    public function isFinal()
    {
        return in_array($this->status, self::FINAL_STATUSES, true);
    }

    public function isOverdue()
    {
        return ! $this->isFinal()
            && $this->expected_delivery
            && $this->expected_delivery->lt(Carbon::today());
    }

    public function serviceLabel()
    {
        return __(self::SERVICES[$this->service_type] ?? $this->service_type);
    }

    public function paymentLabel()
    {
        return __(self::PAYMENTS[$this->payment_method] ?? $this->payment_method);
    }

    public function payerLabel()
    {
        return __(self::PAYERS[$this->shipping_payer] ?? $this->shipping_payer);
    }

    public function packageLabel()
    {
        return __(self::PACKAGE_TYPES[$this->package_type] ?? $this->package_type);
    }

    /**
     * Calculates the total and the amount to collect from the receiver:
     * total = shipping fee + goods price
     * collection = (goods price if COD) + (shipping fee if paid by the receiver)
     */
    public static function computeAmounts($shippingCost, $goodsValue, $paymentMethod, $payer)
    {
        $shippingCost = (float) $shippingCost;
        $goodsValue = (float) $goodsValue;

        $collect = 0;
        if ($paymentMethod === 'cod') {
            $collect += $goodsValue;
        }
        if ($payer === 'receiver') {
            $collect += $shippingCost;
        }

        return [
            'total_amount' => round($shippingCost + $goodsValue, 2),
            'cod_amount' => round($collect, 2),
        ];
    }

    /* ---------- Business logic ---------- */

    public static function generateTrackingNumber()
    {
        do {
            $number = 'SH'.date('ymd').strtoupper(Str::random(6));
        } while (static::where('tracking_number', $number)->exists());

        return $number;
    }

    /** Expected delivery date by service type */
    public static function expectedDateFor($serviceType)
    {
        $days = config('shipping.delivery_days.'.$serviceType, 4);

        return Carbon::today()->addDays($days);
    }

    /**
     * Changes the shipment status, logs the history and sends notifications.
     */
    public function changeStatus($status, $user = null, $note = null, $location = null)
    {
        $this->status = $status;

        if ($status === 'delivered') {
            $this->delivered_at = Carbon::now();
        }

        $this->save();

        $this->histories()->create([
            'user_id' => optional($user)->id,
            'status' => $status,
            'note' => $note,
            'location' => $location,
        ]);

        $type = $status === 'delivered' ? 'success' : (in_array($status, ['returned', 'cancelled'], true) ? 'danger' : 'info');

        UserNotification::notify(
            $this->stakeholderIds(optional($user)->id),
            __('Update shipment status').' '.$this->tracking_number,
            __('New status:').' '.$this->statusLabel(),
            route('shipments.show', $this->id),
            $type
        );
    }

    /**
     * Users concerned with the shipment: general managers + branch managers + creator + courier.
     */
    public function stakeholderIds($exceptUserId = null)
    {
        $ids = User::where('is_active', true)
            ->where(function ($q) {
                $q->whereHas('roles', function ($r) {
                    $r->where('roles.slug', 'admin');
                });
                if ($this->branch_id) {
                    $q->orWhere(function ($q2) {
                        $q2->whereHas('roles', function ($r) {
                            $r->where('roles.slug', 'branch_manager');
                        })->whereHas('branches', function ($b) {
                            $b->where('branches.id', $this->branch_id);
                        });
                    });
                }
            })
            ->pluck('id');

        $ids = $ids->push($this->created_by);

        if ($this->courier && $this->courier->user_id) {
            $ids = $ids->push($this->courier->user_id);
        }

        return $ids->filter(function ($id) use ($exceptUserId) {
            return $id && (int) $id !== (int) $exceptUserId;
        })->unique()->values();
    }
}
