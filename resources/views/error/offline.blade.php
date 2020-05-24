@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">It looks like the server is down</div>
                    <div class="panel-body">
                        Please check these resources and try again later :
                        <ul>
                            <li><a href="https://eve-offline.net/?server=tranquility" target="_blank" title="EVE Offline">eve-offline.com</a></li>
                            <li><a href="https://twitter.com/eve_status?lang=en" target="_blank" title="EVE Status">@EVE_status (twitter)</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection