<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Track shipment') }} - {{ __(config('app.name')) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f4f6fb; }
        .hero { background: linear-gradient(135deg, #0a2a5e, #1565d8); color: #fff; padding: 3rem 0 5rem; }
        .track-card { margin-top: -3rem; border: 0; border-radius: 1rem; box-shadow: 0 10px 30px rgba(16,24,40,.12); }
        .timeline { position: relative; padding-inline-start: 1.5rem; }
        .timeline::before { content: ''; position: absolute; inset-inline-start: 7px; top: 6px; bottom: 6px; width: 2px; background: #dfe3ee; }
        .timeline-item { position: relative; padding-bottom: 1.25rem; }
        .timeline-item::before { content: ''; position: absolute; inset-inline-start: -1.5rem; top: 5px; width: 16px; height: 16px; border-radius: 50%; background: #fff; border: 3px solid #adb5bd; }
        .timeline-item.first::before { background: #0d6efd; border-color: #0d6efd; }
    </style>
</head>
<body>
<div class="hero text-center">
    <div class="container position-relative">
        <a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="btn btn-sm btn-light position-absolute top-0 end-0 fw-semibold"><i class="bi bi-translate"></i> {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a>
        <i class="bi bi-box-seam-fill" style="font-size:3rem"></i>
        <h2 class="mt-2">{{ __('Track your shipment moment by moment') }}</h2>
        <p class="mb-0 opacity-75">{{ __('Enter the tracking number to see the shipment status and location') }}</p>
    </div>
</div>
<div class="container pb-5" style="max-width: 760px">
    <div class="card track-card p-4">
        <form method="get" action="{{ route('tracking') }}" class="input-group input-group-lg">
            <input type="text" name="number" value="{{ $number }}" class="form-control" placeholder="{{ __('Example') }}: SH250101ABC123" required>
            <button class="btn btn-primary px-4"><i class="bi bi-search"></i> {{ __('Track') }}</button>
        </form>
    </div>

    @if($number !== '')
        @if($shipment)
            <div class="card mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <div class="text-muted small">{{ __('Tracking number') }}</div>
                            <h5 class="mb-0">{{ $shipment->tracking_number }}</h5>
                        </div>
                        <div>
                            <span class="badge fs-6 bg-{{ $shipment->statusColor() }} {{ in_array($shipment->statusColor(), ['warning','info']) ? 'text-dark' : '' }}">{{ $shipment->statusLabel() }}</span>
                        </div>
                    </div>
                    <div class="row g-3 small mb-3">
                        <div class="col-6 col-md-3"><div class="text-muted">{{ __('From') }}</div><strong>{{ $shipment->origin_city }}</strong></div>
                        <div class="col-6 col-md-3"><div class="text-muted">{{ __('To') }}</div><strong>{{ $shipment->receiver_city }}</strong></div>
                        <div class="col-6 col-md-3"><div class="text-muted">{{ __('Service') }}</div><strong>{{ $shipment->serviceLabel() }}</strong></div>
                        <div class="col-6 col-md-3"><div class="text-muted">{{ __('Expected delivery') }}</div><strong>{{ optional($shipment->expected_delivery)->format('Y-m-d') ?: '-' }}</strong></div>
                    </div>
                    <hr>
                    <h6 class="mb-3">{{ __('Shipment tracking history') }}</h6>
                    <div class="timeline">
                        @foreach($shipment->histories as $i => $h)
                            <div class="timeline-item {{ $i === 0 ? 'first' : '' }}">
                                <div class="fw-semibold">{{ $h->statusLabel() }}</div>
                                @if($h->location)<div class="small text-muted"><i class="bi bi-geo-alt"></i> {{ $h->location }}</div>@endif
                                <div class="small text-muted">{{ $h->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning mt-4"><i class="bi bi-exclamation-triangle"></i> {{ __('No shipment was found with this number. Check the number and try again') }}.</div>
        @endif
    @endif

    <div class="text-center mt-4">
        <a href="{{ route('login') }}" class="text-decoration-none"><i class="bi bi-box-arrow-in-left"></i> {{ __('Staff login') }}</a>
    </div>
</div>
</body>
</html>
