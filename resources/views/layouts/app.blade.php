<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Signatures') }}</title>

    <!-- Styles -->
    <link href='https://fonts.googleapis.com/css?family=Miriam+Libre:400,700|Source+Sans+Pro:200,400,700,600,400italic,700italic' rel='stylesheet' type='text/css'>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @yield('view.styles')

    {{--@include('partials.analytics')--}}
    @include('partials.adsense')
</head>
<body class="bg-dark">
    <div id="app" class="bg-light">
        <header>
            <div class="navbar navbar-dark bg-dark box-shadow">
                <div class="container d-flex justify-content-between">
                    <a href="{{ route('index') }}" class="navbar-brand d-flex align-items-center">
                        <strong>{{ config('app.name', 'Signatures') }}</strong>
                    </a>
                    @yield('sso')
                </div>
            </div>
        </header>
        @yield('content')
        <div id="push"></div>
    </div>

    @include('partials.footer')

    <div class="updateLoader"><img src="/img/ajax-loader-white.gif" alt="" /></div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('view.scripts')
</body>
</html>
