@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    Name : {{ $character->characterName }} <img src="https://images.evetech.net/characters/{{ $character->characterId }}/portrait?size=64" alt="{{ $character->characterName }} portrait" width="64" height="64">
                    Location: system {{ $system->systemName() }}, region {{ $system->regionName() }}, constellation {{ $system->constellationName() }}
                </div>
            </div>
            <div class="col-md-12">
                {!! Form::open(['route' => 'signatures.analyze', 'method' => 'post']) !!}
                    {{ csrf_field() }}
                    {{ Form::label('rawdata', 'Copy & paste scan data here') }}
                    {!! Form::textarea('rawdata', null, ['class'=>'form-control']) !!}
                    {!! Form::submit('Submit') !!}
                {!! Form::close() !!}
                <button class="btnScrollToTop" title="Top"><img src="/images/arrow-up.png" width="50" height="50" alt=""></button>
            </div>
        </div>
    </div>
@endsection

@section('view.scripts')
    <script>
        $(document).ready(function(){
            window.onscroll = function() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    $('.btnScrollToTop').show();
                } else {
                    $('.btnScrollToTop').hide();
                }
            };
            $('.btnScrollToTop').on('click', function(){
                document.body.scrollTop = 0; // For Safari
                document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
            });
        });
    </script>
@endsection

@section('sso')
    @include('partials.sso', ['size' => 'small'])
@endsection