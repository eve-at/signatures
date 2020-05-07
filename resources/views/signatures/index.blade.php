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
                @if ($signatures)
                    <label for="anomalyGroup">Filter by Group</label>
                    <select name="anomalyGroup" id="anomalyGroup">
                        <option value="">All</option>
                        <option value="Wormhole">Wormhole</option>
                        <option value="Combat Site">Combat Site</option>
                        <option value="Ore Site">Ore Site</option>
                        <option value="Gas Site">Gas Site</option>
                        <option value="Data Site">Data Site</option>
                        <option value="Relic Site">Relic Site</option>
                    </select>
                    <span class="js-filter-info" style="display:none;"></span>
                    <table>
                        <thead>
                            <tr>
                                <th rowspan="2">ID</th>
                                <th rowspan="2">Group</th>
                                <th rowspan="2">Wormhole</th>
                                <th colspan="2">Other side WH</th>
                                <th rowspan="2">Estimated Life</th>
                                <th rowspan="2">Updated</th>
                            </tr>
                            <tr>
                                <th>ID</th>
                                <th>System</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($signatures as $signature)
                                @php
                                    $expires = '';
                                    if ("Wormhole" == $signature->anomalyGroup):
                                        $expires = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($signature->expires_at));
                                        if ($expires < 2):
                                            $expires = '< an hour';
                                        else:
                                            $expires = '< ' . $expires . ' hours';
                                        endif;
                                    endif;
                                @endphp
                                <tr data-signature="{{ $signature->signatureId }}" data-anomalyGroup="{{ $signature->anomalyGroup }}">
                                    <td>{{ $signature->enterCode }}</td>
                                    <td>{{ $signature->anomalyGroup ?: '<not scanned>' }}</td>
                                    @if ('Wormhole' == $signature->anomalyGroup)
                                        <td>
                                            @if ($arrEveData['characterId'] == $signature->characterId)
                                                <div class="ui-widget">
                                                    <input type="text"
                                                           class="js-enterAnomaly"
                                                           placeholder="Wormhole ID, ex. N110"
                                                           name="enterAnomaly_{{ $signature->signatureId }}"
                                                           value="{{ $signature->enterAnomaly() }}">
                                                </div>
                                                <div class="js-anomalyStaticInfo">
                                                    @foreach ($anomalyDynamic as $anomalyInfoKey => $anomalyInfoValues)
                                                        <label for="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">{{ $anomalyInfoKey }}</label>
                                                        <select name="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}" id="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">
                                                            <option value="">Select</option>
                                                            @foreach ($anomalyInfoValues as $anomalyInfoValue)
                                                                @php $selected = $signature->{'anomaly' . $anomalyInfoKey} === $anomalyInfoValue ? 'selected="selected"' : ''; @endphp
                                                                <option value="{{ $anomalyInfoValue }}" {{ $selected }}>{{ $anomalyInfoValue }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endforeach
                                                </div>
                                                <div class="js-anomalyStaticInfo {{ ($signature->enterAnomaly() == "K162" ? '' : 'js-hidden') }}">
                                                    <div class="ui-widget">
                                                        <input type="text"
                                                               class="js-exitAnomaly"
                                                               placeholder="Other side ID ?, ex. N110"
                                                               name="exitAnomaly_{{ $signature->signatureId }}"
                                                               value="{{ $signature->exitAnomaly() }}">
                                                    </div>
                                                    OR<br>
                                                    @foreach ($anomalyStatic as $anomalyInfoKey => $anomalyInfoValues)
                                                        <label for="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">{{ $anomalyInfoKey }}</label>
                                                        <select name="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}" id="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">
                                                            <option value="">Select</option>
                                                            @foreach ($anomalyInfoValues as $anomalyInfoValue)
                                                                @php $selected = $signature->{'anomaly' . $anomalyInfoKey} === $anomalyInfoValue ? 'selected="selected"' : ''; @endphp
                                                                <option value="{{ $anomalyInfoValue }}" {{ $selected }}>{{ $anomalyInfoValue }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endforeach
                                                </div>
                                            @elseif ($signature->anomalyId)
                                                {{ $signature->enterAnomaly() }}
                                                @if ("K162" == $signature->enterAnomaly())
                                                    leads to {{ $signature->exitAnomaly() ?? '' }}
                                                @endif
                                                <span style="cursor:pointer;">+</span>&nbsp;<span style="cursor:pointer;">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($arrEveData['characterId'] == $signature->characterId)
                                                <div class="ui-widget">
                                                    <input type="text"
                                                           class="js-exitCode"
                                                           placeholder="Other side WH ID, ex. ZFD-231"
                                                           name="exitCode_{{ $signature->signatureId }}"
                                                           value="{{ $signature->exitCode }}">
                                                </div>
                                            @elseif ($signature->exitCode)
                                                {{ $signature->exitCode }}
                                                <span style="cursor:pointer;">+</span>&nbsp;<span style="cursor:pointer;">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($arrEveData['characterId'] == $signature->characterId)
                                                <div class="ui-widget">
                                                    <input type="text"
                                                           class="js-exitSystem"
                                                           placeholder="Other side Solar System"
                                                           name="exitSystem_{{ $signature->signatureId }}"
                                                           value="{{ $signature->exitSystem() }}">
                                                </div>
                                            @elseif ($signature->exitSystem)
                                                {{ $signature->exitSystem() }}
                                                <span style="cursor:pointer;">+</span>&nbsp;<span style="cursor:pointer;">-</span>
                                            @endif
                                        </td>
                                    @else
                                        <td colspan="3">{{-- TODO: Anomaly name here --}}</td>
                                    @endif
                                    <td>{{ $expires }}</td>
                                    <td>{{ $signature->character()->characterName }}, {{ \Carbon\Carbon::parse($signature->updated_at)->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="col-md-12">
                {!! Form::open(['route' => 'signatures.analyze', 'method' => 'put']) !!}
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
        $(document).ready(function() {
            $('.ui-widget input[type="text"]').click(function () {
                $(this).select();
            });

            $('.js-exitCode').inputmask({
                mask: "a{3}-[9{3}]",
                greedy: false,
                onBeforePaste: function (pastedValue, opts) {
                    return pastedValue.toUpperCase();
                },
            });

            $(document).on('change', '#anomalyGroup', function () {
                if ($('#anomalyGroup').val()) {
                    var trAll = $('tr[data-anomalyGroup]');
                    var trToShow = $('tr[data-anomalyGroup="' + $('#anomalyGroup').val() + '"]');
                    trAll.hide();
                    trToShow.show();

                    $('.js-filter-info').html((trAll.length - trToShow.length) + ' filtered out');
                    $('.js-filter-info').show();
                } else {
                    $('tr[data-anomalyGroup]').show();
                    $('.js-filter-info').hide();
                }
            });

            var anomalyId_cache = {};
            $(".js-enterAnomaly,.js-exitAnomaly").autocomplete({
                minLength: 1,
                source: function( request, response ) {
                    var term = request.term;
                    if ( term in anomalyId_cache ) {
                        response( anomalyId_cache[ term ] );
                        return;
                    }

                    $.getJSON( "{{ route('ajax.wormholes') }}", request, function( data, status, xhr ) {
                        anomalyId_cache[ term ] = data;
                        response( data );
                    });
                },
                /*change: function (event, ui) {
                    ajaxSaveSignatureInfo(event);
                },*/
                select: function( event, ui ) {
                    if (! ui.item.id) {
                        event.preventDefault();
                        return;
                    }
                    event.target.value = ui.item.value;
                    ajaxSaveSignatureInfo(event);

                    if (event.target.classList.contains('js-enterAnomaly')) {
                        if (ui.item.value == "K162") {
                            $(event.target).closest('td').find('.js-anomalyStaticInfo').removeClass('js-hidden');
                        } else {
                            $(event.target).closest('td').find('.js-anomalyStaticInfo').addClass('js-hidden');
                        }
                    }
                }
            });

            var exitSystem_cache = {};
            $(".js-exitSystem").autocomplete({
                minLength: 3,
                source: function( request, response ) {
                    var term = request.term;
                    if ( term in exitSystem_cache ) {
                        response( exitSystem_cache[ term ] );
                        return;
                    }

                    $.getJSON( "{{ route('ajax.systems') }}", request, function( data, status, xhr ) {
                        if (! data.length) {
                            data = [
                                {
                                    id: null,
                                    label: 'No matches found',
                                    value: response.term
                                }
                            ];
                        }
                        exitSystem_cache[ term ] = data;
                        response( data );
                    });
                },
                /*change: function (event, ui) {
                    ajaxSaveSignatureInfo(event);
                },*/
                select: function( event, ui ) {
                    if (! ui.item.id) {
                        event.preventDefault();
                        return;
                    }
                    event.target.value = ui.item.value;
                    ajaxSaveSignatureInfo(event);
                }
            });

            function ajaxSaveSignatureInfo(event) {
                var element = $(event.target);
                console.log('element', element, element.data('solarSystemId'));

                $.ajax({
                    url: "{{ route('ajax.signature') }}",
                    method: "PUT",
                    dataType: "json",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "field": event.target.name,
                        "value": element.val(),
                    }
                }).done(function(data) {
                    console.log(data);
                    $( this ).addClass( "done" );
                });
            }

            $(document).on('change', '.js-exitCode,.js-anomalyDynamicInfo select,.js-anomalyStaticInfo select', ajaxSaveSignatureInfo); //,.js-exitSystem
            $(document).on('keypress', '.js-exitCode', function (event) { //,.js-exitSystem
                if (event.keyCode === 13) {
                    event.preventDefault();
                    this.blur(); // will trigger "change"
                }
            });

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