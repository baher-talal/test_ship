<?php

namespace App\Http\Controllers;

use App\Models\Courier;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $base = function () use ($user) {
            return Shipment::forUser($user);
        };

        $stats = [
            'total' => $base()->count(),
            'today' => $base()->whereDate('shipments.created_at', Carbon::today())->count(),
            'active' => $base()->active()->count(),
            'delivered_month' => $base()->where('shipments.status', 'delivered')
                ->whereMonth('shipments.delivered_at', Carbon::now()->month)
                ->whereYear('shipments.delivered_at', Carbon::now()->year)->count(),
            'overdue' => $base()->overdue()->count(),
            'cod_pending' => (float) $base()->where('shipments.cod_amount', '>', 0)
                ->where('shipments.status', 'delivered')->sum('shipments.cod_amount'),
        ];

        if (! $user->isCourier()) {
            $stats['revenue_month'] = (float) $base()->where('shipments.status', '!=', 'cancelled')
                ->whereMonth('shipments.created_at', Carbon::now()->month)
                ->whereYear('shipments.created_at', Carbon::now()->year)->sum('shipments.shipping_cost');
            $stats['unpaid_invoices'] = (float) Invoice::forUser($user)->where('status', 'unpaid')->sum('total');
            $stats['couriers_available'] = Courier::forUser($user)->where('is_active', true)
                ->where('status', 'available')->count();
        }

        // Status distribution
        $byStatus = $base()->selectRaw('shipments.status as status, COUNT(*) as c')
            ->groupBy('shipments.status')->pluck('c', 'status');
        $statusChart = ['labels' => [], 'data' => []];
        foreach (Shipment::STATUSES as $key => $meta) {
            $statusChart['labels'][] = __($meta['label']);
            $statusChart['data'][] = (int) ($byStatus[$key] ?? 0);
        }

        // Last 7 days
        $from = Carbon::today()->subDays(6);
        $daily = $base()->selectRaw('DATE(shipments.created_at) as d, COUNT(*) as c')
            ->where('shipments.created_at', '>=', $from)
            ->groupBy('d')->pluck('c', 'd');
        $dayChart = ['labels' => [], 'data' => []];
        for ($i = 0; $i < 7; $i++) {
            $day = $from->copy()->addDays($i);
            $dayChart['labels'][] = $day->format('m/d');
            $dayChart['data'][] = (int) ($daily[$day->toDateString()] ?? 0);
        }

        $latest = $base()->with(['customer', 'courier'])->latest()->take(8)->get();
        $overdueList = $base()->overdue()->with('customer')->orderBy('expected_delivery')->take(5)->get();

        return view('dashboard', compact('stats', 'statusChart', 'dayChart', 'latest', 'overdueList', 'byStatus'));
    }
}
