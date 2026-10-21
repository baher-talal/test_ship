<?php

return [
    // Default invoice tax rate
    'tax_rate' => (float) env('INVOICE_TAX_RATE', 14),

    // Expected delivery days by service type
    'delivery_days' => [
        'standard' => 4,
        'express' => 2,
        'same_day' => 0,
    ],

    // Base price per service type (used for the automatic estimate in the UI)
    'base_price' => ['standard' => 30, 'express' => 50, 'same_day' => 90],
    'price_per_kg' => ['standard' => 5, 'express' => 8, 'same_day' => 15],
];
