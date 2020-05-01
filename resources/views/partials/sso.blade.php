
@php
    $state = "new-char";
    $url = "https://login.eveonline.com/oauth/authorize/?response_type=code&redirect_uri=" . route("sso") . "&client_id=" . config('app.eve_app_id') . "&scope=" . config('app.eve_app_scopes') . "&state=" . $state;
@endphp

@if($size == 'small')
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <a href="{{ $url }}" title="Log in with another character"><img src="{{ asset('img/ssologin.png') }}" alt="Log in with EVE Online" /></a>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-md-12 center">
            <a href="{{ $url }}" title="Log in with EVE Online"><img src="{{ asset('img/ssologin.png') }}" alt="Log in with EVE Online" /></a>
        </div>
    </div>
@endif