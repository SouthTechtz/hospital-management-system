@if (session('status'))
    <div class="alert alert-success" role="status">{{ session('status') }}</div>
@endif

@if (session('apiKeyOnce'))
    <div class="alert alert-warning" role="status">
        <strong>{{ __('admin.api_key_once') }}</strong>
        <div class="mono">{{ session('apiKeyOnce') }}</div>
        <div class="small">{{ __('admin.api_key_once_hint') }}</div>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="alert-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif