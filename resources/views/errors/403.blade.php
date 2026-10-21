<!DOCTYPE html><html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"><head><meta charset="utf-8"><title>{{ __('Unauthorized') }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet"></head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh"><div class="container text-center">
<h1 class="display-1 text-danger">403</h1><p class="lead">{{ $exception->getMessage() ?: __('You do not have permission to access this page').'.' }}</p>
<a href="{{ url('/dashboard') }}" class="btn btn-primary">{{ __('Back to dashboard') }}</a></div></body></html>
