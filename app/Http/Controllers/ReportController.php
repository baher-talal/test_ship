<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Courier;
use App\Models\Invoice;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        [$from, $to] = $this->range($request);

        $base = function () use ($request, $from, $to) {
            return $this->filtered($request, $from, $to);
        };

        $summary = [
            'total' => $base()->count(),
            'delivered' => $base()->where('shipments.status', 'delivered')->count(),
            'returned' => $base()->where('shipments.status', 'returned')->count(),
            'cancelled' => $base()->where('shipments.status', 'cancelled')->count(),
            'revenue' => (float) $base()->where('shipments.status', '!=', 'cancelled')->sum('shipments.shipping_cost'),
            'cod_collected' => (float) $base()->where('shipments.status', 'delivered')
                ->where('shipments.cod_amount', '>', 0)->sum('shipments.cod_amount'),
            'goods_value' => (float) $base()->where('shipments.status', '!=', 'cancelled')->sum('shipments.goods_value'),
            'weight' => (float) $base()->sum('shipments.weight'),
        ];
        $summary['delivery_rate'] = $summary['total'] > 0 ? round($summary['delivered'] / $summary['total'] * 100, 1) : 0;

        $byStatus = $base()->selectRaw('shipments.status as status, COUNT(*) as c, SUM(shipments.shipping_cost) as revenue')
            ->groupBy('shipments.status')->get()->keyBy('status');

        $byCourier = $base()->whereNotNull('shipments.courier_id')
            ->selectRaw("shipments.courier_id, COUNT(*) as total,
                SUM(CASE WHEN shipments.status = 'delivered' THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN shipments.status = 'returned' THEN 1 ELSE 0 END) as returned")
            ->groupBy('shipments.courier_id')->orderByDesc('total')->get();
        $courierNames = Courier::whereIn('id', $byCourier->pluck('courier_id'))->pluck('name', 'id');

        $byBranch = collect();
        if ($user->needsBranchChoice()) {
            $byBranch = $this->filtered($request, $from, $to)->reorder()
                ->selectRaw('shipments.branch_id, COUNT(*) as total, SUM(shipments.shipping_cost) as revenue')
                ->groupBy('shipments.branch_id')->get();
            $branchNames = Branch::pluck('name', 'id');
            $byBranch->each(function ($row) use ($branchNames) {
                $row->name = $branchNames[$row->branch_id] ?? __('No branch');
            });
        }

        $byCity = $base()->selectRaw('shipments.receiver_city as city, COUNT(*) as c')
            ->groupBy('shipments.receiver_city')->orderByDesc('c')->take(8)->get();

        $invoices = [
            'paid' => (float) Invoice::forUser($user)->where('status', 'paid')
                ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])->sum('total'),
            'unpaid' => (float) Invoice::forUser($user)->where('status', 'unpaid')
                ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])->sum('total'),
        ];

        return view('reports.index', [
            'summary' => $summary,
            'byStatus' => $byStatus,
            'byCourier' => $byCourier,
            'courierNames' => $courierNames,
            'byBranch' => $byBranch,
            'byCity' => $byCity,
            'invoices' => $invoices,
            'from' => $from,
            'to' => $to,
            'branches' => $user->needsBranchChoice() ? $user->selectableBranches() : collect(),
            'couriers' => Courier::forUser($user)->orderBy('name')->get(),
        ]);
    }

    /** Export shipments as CSV (Excel-compatible, UTF-8 encoded) */
    public function export(Request $request)
    {
        [$from, $to] = $this->range($request);
        $query = $this->filtered($request, $from, $to)->with(['customer', 'courier', 'branch'])->orderBy('shipments.id');

        $filename = 'shipments-'.$from->toDateString().'_'.$to->toDateString().'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                __('Tracking number'), __('Date'), __('Branch'), __('Customer'), __('Receiver'), __('Receiver phone'), __('City'), __('Service'),
                __('Weight'), __('Pieces'), __('Shipping fee'), __('Goods price'), __('Total'), __('Shipping payer'), __('Payment method'), __('To collect'), __('Status'), __('Courier'),
            ]);
            $query->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $s) {
                    fputcsv($out, [
                        $s->tracking_number, $s->created_at->format('Y-m-d H:i'), optional($s->branch)->name,
                        optional($s->customer)->name, $s->receiver_name, $s->receiver_phone, $s->receiver_city,
                        $s->serviceLabel(), $s->weight, $s->pieces, $s->shipping_cost, $s->goods_value, $s->total_amount,
                        $s->payerLabel(), $s->paymentLabel(), $s->cod_amount, $s->statusLabel(), optional($s->courier)->name,
                    ]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function range(Request $request)
    {
        $from = $request->filled('from') ? Carbon::parse($request->query('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->query('to'))->endOfDay() : Carbon::now()->endOfDay();

        return [$from, $to];
    }

    protected function filtered(Request $request, Carbon $from, Carbon $to)
    {
        $user = $request->user();
        $query = Shipment::forUser($user)->whereBetween('shipments.created_at', [$from, $to]);

        if ($user->needsBranchChoice() && $request->filled('branch_id')) {
            $query->where('shipments.branch_id', $request->query('branch_id'));
        }
        if ($request->filled('courier_id')) {
            $query->where('shipments.courier_id', $request->query('courier_id'));
        }

        return $query;
    }
}
