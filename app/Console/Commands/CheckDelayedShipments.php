<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CheckDelayedShipments extends Command
{
    protected $signature = 'shipments:check-delays';

    protected $description = 'Send alerts for shipments past their expected delivery date (once a day per shipment)';

    public function handle()
    {
        $count = 0;
        $today = Carbon::today();

        Shipment::overdue()->with('courier')->chunk(200, function ($shipments) use (&$count, $today) {
            foreach ($shipments as $shipment) {
                $ids = $shipment->stakeholderIds();
                $url = route('shipments.show', $shipment->id);

                $already = UserNotification::where('type', 'danger')
                    ->where('url', $url)
                    ->where('title', 'like', __('Shipment delayed').'%')
                    ->whereDate('created_at', $today)
                    ->pluck('user_id');

                $ids = $ids->diff($already);
                if ($ids->isEmpty()) {
                    continue;
                }

                $days = $shipment->expected_delivery->diffInDays($today);
                UserNotification::notify(
                    $ids,
                    __('Shipment delayed').' '.$shipment->tracking_number,
                    __('Overdue').' '.$days.' '.__('days past the expected date').' ('.$shipment->expected_delivery->format('Y-m-d').')',
                    $url,
                    'danger'
                );
                $count++;
            }
        });

        $this->info("Alerts sent for {$count} delayed shipment(s).");

        return 0;
    }
}
