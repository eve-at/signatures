@extends('layouts.app')

@section('content')
    <div class="container">
        @if (isset($errors) && count($errors))
            <ul>
                @foreach($errors as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
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
                                <th>ID</th>
                                <th>Group</th>
                                <th colspan="2">Wormhole</th>
                                <th>Other side System</th>
                                <th>Other side ID</th>
                                <th>Estimated Life</th>
                                <th>Updated</th>
                                <th>Options</th>
                            </tr>
                            <tr>

                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($signatures as $signature): ?>
                                @php
                                    $ratings = [[],[]];
                                    foreach ($signature['ratings'] as $rating):
                                        // skip disliked signatures
                                        if ($rating['characterId'] == $character->characterId && ! $rating['liked']):
                                            continue 2;
                                        endif;
                                        $ratings[(int) $rating['liked']][] = [
                                            'id' => $rating['characterId'],
                                            'name' => $rating['characterName'],
                                        ];
                                    endforeach;

                                    $expires = '';
                                    if ("Wormhole" == $signature->anomalyGroup):
                                        $expires = \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($signature->expires_at), false);
                                        if ($expires < 0):
                                            $expires = 'expired';
                                        elseif ($expires < 2):
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
                                        @php
                                            $showStatic = ! $signature->enterAnomaly() || ($signature->enterAnomaly() == "K162" && ! $signature->exitAnomaly());
                                        @endphp
                                        @if ($arrEveData['characterId'] == $signature->characterId)
                                            <td>
                                                <label for="enterAnomaly_{{ $signature->signatureId }}">Enter WH</label>
                                                <select class="js-enterAnomaly chosen-select" name="enterAnomaly_{{ $signature->signatureId }}" id="enterAnomaly_{{ $signature->signatureId }}">
                                                    <option value="">Select an option</option>
                                                    @foreach ($wormholes as $wormhole)
                                                        <option value="{{ $wormhole->wormholeName }}" {{ $signature->enterAnomaly() == $wormhole->wormholeName ? 'selected' : '' }}>{{ $wormhole->wormholeName }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="js-anomalyStaticInfo {{ ($signature->enterAnomaly() == "K162" ? '' : 'js-hidden') }}">
                                                    <label for="exitAnomaly_{{ $signature->signatureId }}">Exit WH</label>
                                                    <select class="js-exitAnomaly chosen-select" name="exitAnomaly_{{ $signature->signatureId }}" id="exitAnomaly_{{ $signature->signatureId }}">
                                                        <option value="">Select an option</option>
                                                        @foreach ($wormholes as $wormhole)
                                                            <option value="{{ $wormhole->wormholeName }}" {{ $signature->exitAnomaly() == $wormhole->wormholeName ? 'selected' : '' }}>{{ $wormhole->wormholeName }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="js-anomalyStaticInfoAlternative {{ $showStatic ? '' : 'js-hidden' }}">
                                                    OR<br>
                                                    @foreach ($anomalyStatic as $anomalyInfoKey => $anomalyInfoValues)
                                                        <label for="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">{{ $anomalyInfoKey }}</label>
                                                        <select name="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}"
                                                                class="js-anomaly{{ $anomalyInfoKey }}"
                                                                data-value="{{ $signature->{'anomaly' . $anomalyInfoKey} }}"
                                                                id="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">
                                                            <option value="">Select an option</option>
                                                            @foreach ($anomalyInfoValues as $anomalyInfoValue)
                                                                @php $selected = $signature->{'anomaly' . $anomalyInfoKey} === $anomalyInfoValue ? 'selected="selected"' : ''; @endphp
                                                                <option value="{{ $anomalyInfoValue }}" {{ $selected }}>{{ $anomalyInfoValue }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endforeach
                                                </div>
                                                <span class="js-anomalySummary">{{ $signature->summary() }}</span>
                                            </td>
                                            <td>
                                                <div class="js-anomalyDynamicInfo">
                                                    @foreach ($anomalyDynamic as $anomalyInfoKey => $anomalyInfoValues)
                                                        <label for="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">{{ $anomalyInfoKey }}</label>
                                                        <select name="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}"
                                                                class="js-anomaly{{ $anomalyInfoKey }}"
                                                                data-value="{{ $signature->{'anomaly' . $anomalyInfoKey} }}"
                                                                id="anomaly{{ $anomalyInfoKey }}_{{ $signature->signatureId }}">
                                                            <option value="">Select an option</option>
                                                            @foreach ($anomalyInfoValues as $anomalyInfoValue)
                                                                @php $selected = $signature->{'anomaly' . $anomalyInfoKey} === $anomalyInfoValue ? 'selected="selected"' : ''; @endphp
                                                                <option value="{{ $anomalyInfoValue }}" {{ $selected }}>{{ $anomalyInfoValue }}</option>
                                                            @endforeach
                                                        </select>
                                                    @endforeach
                                                </div>
                                            </td>
                                        @else
                                            <td colspan="2">
                                                <span class="anomalySummary">{!! $signature->summary() !!}</span>
                                            </td>
                                        @endif
                                        <td width="150">
                                            @if ($arrEveData['characterId'] == $signature->characterId)
                                                <label for="exitSystem_{{ $signature->signatureId }}">Other side Solar System</label>
                                                <select name="exitSystem_{{ $signature->signatureId }}" id="exitSystem_{{ $signature->signatureId }}"
                                                        class="js-exitSystem">
                                                    @if ($exitSystem = $signature->exitSystem())
                                                        <option value="{{ $exitSystem->solarSystemID }}" selected="selected">{!! $exitSystem->toInfoString() !!}</option>
                                                    @endif
                                                    
                                                </select>
                                                {{--<div class="ui-widget">
                                                    <input type="text"
                                                           class="js-exitSystem"
                                                           placeholder="Other side Solar System"
                                                           data-value="{{ $signature->exitSystem() }}"
                                                           name="exitSystem_{{ $signature->signatureId }}"
                                                           value="{{ $signature->exitSystem() }}">
                                                </div>--}}
                                            @elseif ($signature->exitSystem)
                                                {{ $signature->exitSystem() }}
                                            @endif
                                        </td>
                                        <td width="50">
                                            @if ($arrEveData['characterId'] == $signature->characterId)
                                                <div class="ui-widget">
                                                    <input type="text"
                                                           style="width:50px;"
                                                           class="js-exitCode"
                                                           placeholder="Other side WH ID, ex. ZFD-231"
                                                           data-value="{{ $signature->exitCode }}"
                                                           name="exitCode_{{ $signature->signatureId }}"
                                                           value="{{ $signature->exitCode }}">
                                                </div>
                                            @elseif ($signature->exitCode)
                                                {{ $signature->exitCode }}
                                            @endif
                                        </td>
                                    @else
                                        <td colspan="4">{{-- TODO: Anomaly name here --}}</td>
                                    @endif
                                    <td>{{ $expires }}</td>
                                    <td>{{ $signature->character()->characterName }}, {{ \Carbon\Carbon::parse($signature->updated_at)->diffForHumans() }}</td>
                                    <td>
                                        @if ($arrEveData['characterId'] == $signature->characterId)
                                            <a href="#" class="js-remove">Remove (expired)</a>
                                        @else
                                            @php
                                                $dialogLike = "<ul>";
                                                if (count($ratings[1])):
                                                    foreach ($ratings[1] as $r):
                                                        $dialogLike .= '<li><a href="https://evewho.com/character/' . $r['id'] . '" target="_blank"><img src="https://images.evetech.net/characters/' . $r['id'] . '/portrait?size=32" alt="' . $r['name'] . ' portrait" width="32" height="32">' . $r['name'] . '</a></li>';
                                                    endforeach;
                                                endif;
                                                $dialogLike .= "</ul>";
                                                $dialogDislike = "<ul>";
                                                if (count($ratings[0])):
                                                    foreach ($ratings[0] as $r):
                                                        $dialogDislike .= '<li><a href="https://evewho.com/character/' . $r['id'] . '" target="_blank"><img src="https://images.evetech.net/characters/' . $r['id'] . '/portrait?size=32" alt="' . $r['name'] . ' portrait" width="32" height="32">' . $r['name'] . '</a></li>';
                                                    endforeach;
                                                endif;
                                                $dialogDislike .= "</ul>";
                                            @endphp
                                            <a href="#" class="js-like">+1</a>
                                            @if (count($ratings[1]))
                                                &nbsp;
                                                <span style="cursor: pointer;" class="js-dialogLike" data-like="1">({{ count($ratings[1]) }})</span>
                                                <div class="js-dialog" data-like="1" title="Confirmed by" style="display:none;">{!! $dialogLike !!}</div>
                                            @endif
                                            &nbsp;/&nbsp;
                                            <a href="#" class="js-dislike">-1</a>
                                            @if (count($ratings[0]))
                                                &nbsp;
                                                <span style="cursor: pointer;" class="js-dialogLike" data-like="0">({{ count($ratings[0]) }})</span>
                                                <div class="js-dialog" data-like="0" title="Disproved by" style="display:none;">{!! $dialogDislike !!}</div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
    @php
        $aWormholes = [];
        foreach ($wormholes as $wormhole) {
            $aWormholes[$wormhole->wormholeName] = $wormhole->toArray();
        }
    @endphp
    <script>
        var wormholes = JSON.parse('{!! json_encode($aWormholes) !!}');
        $(document).ready(function() {
            $('.ui-widget input[type="text"]').click(function () {
                $(this).select();
            });

            $('select').select2({
                allowClear: true,
                placeholder: 'Select an option',
                width: 'resolve',
            });

            $('.js-exitCode').inputmask({
                mask: "a{3}-[9{3}]",
                greedy: false,
                onBeforePaste: function (pastedValue, opts) {
                    return pastedValue.toUpperCase();
                },
            });

            function updateSummary(tr) {
                var enterAnomaly = tr.find('.js-enterAnomaly').val();
                var exitAnomaly = tr.find('.js-exitAnomaly').val();
                var anomalyMass = tr.find('.js-anomalyMass').val();
                var anomalyTime = tr.find('.js-anomalyTime').val();
                var anomalySize = tr.find('.js-anomalySize').val();
                var anomalyClass = tr.find('.js-anomalyClass').val();

                var staticData = [];
                if (anomalyClass) {
                    staticData.push(anomalyClass);
                }
                if (anomalySize) {
                    staticData.push(anomalySize);
                }

                console.log(enterAnomaly, exitAnomaly, anomalyClass, anomalySize, staticData);
                var summary = [""];
                if (enterAnomaly) {
                    summary[0] = enterAnomaly;
                    if (enterAnomaly === 'K162') {
                        summary[0] += "->";
                        if (exitAnomaly) {
                            summary[0] += exitAnomaly;

                            staticData = [];
                            staticData.push(wormholes[exitAnomaly].wormholeClassShort);
                            staticData.push(wormholes[exitAnomaly].wormholeSize);
                        } else {
                            summary[0] += '?';
                        }
                    } else {
                        staticData = [];
                        staticData.push(wormholes[enterAnomaly].wormholeClassShort);
                        staticData.push(wormholes[enterAnomaly].wormholeSize);
                    }
                }

                if (staticData.length) {
                    summary[0] += " (" + staticData.join(', ') + ")";
                }

                if (anomalyMass) {
                    summary.push('Mass: ' + anomalyMass);
                }

                if (anomalyTime) {
                    summary.push('Time: ' + anomalyTime);
                }

                tr.find('.js-anomalySummary').html(summary.join('<br>'));
            }

            $('tr[data-anomalygroup="Wormhole"]').each(function () {
                updateSummary($(this));
            });

            $(document).on('change', '#anomalyGroup', function (event) {
                if ($(event.target).val()) {
                    var trAll = $('tr[data-anomalyGroup]');
                    var trToShow = $('tr[data-anomalyGroup="' + $(event.target).val() + '"]');
                    trAll.hide();
                    trToShow.show();

                    $('.js-filter-info').html((trAll.length - trToShow.length) + ' filtered out');
                    $('.js-filter-info').show();
                } else {
                    $('tr[data-anomalyGroup]').show();
                    $('.js-filter-info').hide();
                }
            });

            $(document).on('click', '.js-dialogLike', function (event) {
                console.log('.js-dialog[data-like="' + $(event.target).data('like') + '"]', $('.js-dialog [data-like="' + $(event.target).data('like') + '"]').length);
                $('.js-dialog[data-like="' + $(event.target).data('like') + '"]').dialog();
            });

            var anomalyId_cache = {};
            $(document).on("change", ".js-enterAnomaly", function (event) {
                ajaxSaveSignatureInfo(event);

                if (! event.target.value) {
                    $(event.target).closest('td').find('.js-anomalyStaticInfoAlternative').removeClass('js-hidden');
                } else if (event.target.value == "K162") {
                    $(event.target).closest('td').find('.js-anomalyStaticInfo').removeClass('js-hidden');
                    $(event.target).closest('td').find('.js-anomalyStaticInfoAlternative').removeClass('js-hidden');
                } else {
                    $(event.target).closest('td').find('.js-anomalyStaticInfo').addClass('js-hidden');
                    $(event.target).closest('td').find('.js-anomalyStaticInfoAlternative').addClass('js-hidden');
                }
            });

            $(document).on("change", ".js-exitAnomaly", function (event) {
                var alternativeContainer = $(event.target).closest('td').find('.js-anomalyStaticInfoAlternative');
                if (event.target.value == "K162") {
                    $(event.target).val(null).trigger('change');
                    alternativeContainer.removeClass('js-hidden');
                    return;
                }

                if (! event.target.value) {
                    alternativeContainer.removeClass('js-hidden');
                    $(event.target).closest('tr').find('.js-anomalySize').val(null).trigger('change');
                    $(event.target).closest('tr').find('.js-anomalyClass').val(null).trigger('change');
                } else {
                    alternativeContainer.addClass('js-hidden');
                }

                ajaxSaveSignatureInfo(event);
            });

            var exitSystem_cache = {};
            $(".js-exitSystem").select2({
                ajax: {
                    url: "{{ route('ajax.systems') }}",
                    dataType: "json",
                    minimumInputLength: 3,
                    processResults: function (data) {
                        return {
                            results: data.items
                        };
                    }
                },
                width: 'resolve',
                allowClear: true,
                placeholder: 'Other side Solar System',
                language: {
                    searching: function() {
                        return "Enter 3+ characters";
                    }
                },
                searching: function() {
                    return "Please enter 3 or more characters";
                },
            });

            $(document).on("change", ".js-exitSystem", function (event) {
                ajaxSaveSignatureInfo(event);
            });
            /*$(".js-exitSystem").autocomplete({
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
                change: function (event, ui) {
                    event.preventDefault();
                },
                select: function( event, ui ) {
                    console.log(2);
                    if (! ui.item.id) {
                        event.target.value = "";
                        console.log(event.target);
                        //event.preventDefault();
                        return;
                    }
                    event.target.value = ui.item.value;
                    ajaxSaveSignatureInfo(event);
                }
            });*/

            $(document).on('click', '.js-remove', function (event) {
                event.preventDefault();

                var tr = $(event.target).closest('tr');
                $.ajax({
                    url: "{{ route('ajax.signature.delete') }}",
                    method: "DELETE",
                    dataType: "json",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "value": tr.data('signature'),
                    }
                }).done(function(data) {
                    if (data.status == 'ok') {
                        tr.hide("slow", function () {
                            tr.remove();
                        });
                    }
                });
            });

            $(document).on('click', '.js-like,.js-dislike', function (event) {
                event.preventDefault();

                var tr = $(event.target).closest('tr');
                $.ajax({
                    url: "{{ route('ajax.signature.like') }}",
                    method: "POST",
                    dataType: "json",
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": tr.data('signature'),
                        "like": $(event.target).hasClass('js-like') ? 1 : 0,
                    }
                }).done(function(data) {
                    if (data.status == 'ok') {
                        $(event.target).hasClass('js-dislike') && tr.hide("slow", function () {
                            tr.remove();
                        });
                    }
                });
            });

            function ajaxSaveSignatureInfo(event) {
                var element = $(event.target);

                element.data('value', element.val());

                var tr = $(event.target).closest('tr');

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
                    if (data.status == 'ok') {
                        /*tr.find('.js-anomalySize').val(data.data.Size).data('value', data.data.Size);
                        tr.find('.js-anomalyClass').val(data.data.ClassGrouped).data('value', data.data.ClassGrouped);
                        if ($(event.target).hasClass('js-enterAnomaly')) {
                            tr.find('.js-exitAnomaly').data('value', data.data.AnotherSideWormhole)
                                .val(data.data.AnotherSideWormhole);
                            $(event.target).data('size', data.data.Size)
                                .data('class', data.data.Class);
                        } else {
                            tr.find('.js-enterAnomaly').data('value', data.data.AnotherSideWormhole)
                                .val(data.data.AnotherSideWormhole);
                            $(event.target).data('size', data.data.Size)
                                .data('class', data.data.Class);
                        }*/

                        updateSummary(tr);
                    }
                });
            }

            $(document).on('change', '.js-exitCode,.js-anomalyDynamicInfo select,.js-anomalyStaticInfo select,.js-anomalyStaticInfoAlternative', ajaxSaveSignatureInfo);
            $(document).on('keypress', '.js-exitCode', function (event) {
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