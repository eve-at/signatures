@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>EVE Online Wormhole Signatures</h1>
                    </div>
                    <div class="panel-body">
                        <p>{{ config('app.name', 'EVE-AT Assets') }} it a simple tool for Wormhole Signatures.</p>
                        <p>To start, click the button bellow to Log in with EVE Online.</p>
                        @include('partials.sso', ['size' => 'large'])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection