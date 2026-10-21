<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Log in') }} - {{ __(config('app.name')) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #0a2a5e, #1565d8 60%, #4da3ff); display: flex; align-items: center; }
        .login-card { max-width: 420px; width: 100%; border: 0; border-radius: 1rem; box-shadow: 0 20px 50px rgba(0,0,0,.25); }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center">
    <div class="card login-card p-4">
        <div class="text-end mb-2"><a href="{{ route('lang.switch', app()->getLocale() === 'ar' ? 'en' : 'ar') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-translate"></i> {{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}</a></div>
        <div class="text-center mb-4">
            <i class="bi bi-box-seam-fill text-primary" style="font-size:3rem"></i>
            <h4 class="mt-2 mb-0">{{ __(config('app.name')) }}</h4>
            <small class="text-muted">{{ __('Log in to continue') }}</small>
        </div>
        <form method="post" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('Password') }}</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">{{ __('Remember me') }}</label>
            </div>
            <button class="btn btn-primary w-100 py-2">{{ __('Log in') }}</button>
        </form>
        <hr>
        <a href="{{ route('tracking') }}" class="btn btn-outline-secondary w-100"><i class="bi bi-geo-alt"></i> {{ __('Track your shipment') }}</a>
    </div>
</div>
</body>
</html>
