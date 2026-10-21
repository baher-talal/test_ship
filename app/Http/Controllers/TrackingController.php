<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /** Public page to track a shipment by tracking number */
    public function index(Request $request)
    {
        $shipment = null;
        $number = trim((string) $request->query('number'));

        if ($number !== '') {
            $shipment = Shipment::with(['histories', 'branch'])
                ->where('tracking_number', strtoupper($number))
                ->first();
        }

        return view('tracking.index', compact('shipment', 'number'));
    }
}
