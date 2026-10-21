<!DOCTYPE html><html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"><head><meta charset="utf-8"><title>{{ __('Not found') }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh"><div class="container text-center">
<h1 class="display-1 text-secondary">404</h1><p class="lead">{{ __('The requested page was not found') }}.</p>
<a href="{{ url('/') }}" class="btn btn-primary">{{ __('Home page') }}</a></div></body></html>
