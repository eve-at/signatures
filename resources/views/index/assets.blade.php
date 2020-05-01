@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <th class="col-md-12">
                <form action="{{ route('export') }}" method="post">
                    {{ csrf_field() }}
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="center" colspan="4" scope="col">Only packaged items are shown. Citadels are not yet supported.</th>
                            </tr>
                            <tr>
                                <th class='left' colspan="2" scope="col">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Export <span class="js-exportTitle">All</span>
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item js-format" href="javascript:void(0);" data-format="xml">XML</a>
                                            <a class="dropdown-item js-format" href="javascript:void(0);" data-format="json">JSON</a>
                                        </div>
                                    </div>
                                    <strong><span class="js-checkedTotal">0</span> selected</strong>
                                </th>
                                <th class='right' colspan="2" scope="col">
                                    <h2><img class="rounded-circle character-image" src="https://image.eveonline.com/Character/{{ $characterId }}_32.jpg" alt="" />{{ $characterName }}</h2>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($arrAssets['stations'] as $stationId => $stationName)
                            @php
                                $assets = $arrAssets['assets'][$stationId];
                                $arrItemsKeys = array_keys($arrAssets['items']);
                                uksort($assets, function($a, $b) use ($arrItemsKeys) {
                                    return (array_search($a, $arrItemsKeys) < array_search($b, $arrItemsKeys)) ? -1 : 1;
                                });
                            @endphp
                            <tr class="empty">
                                <td colspan="4"></td>
                            </tr>
                            <tr class="station">
                                <th class='left' colspan="4" scope="row">
                                    {{ $stationName . " (" . count($assets) . ")" }}
                                    <div class='float-right'>
                                        <span class="control js-all" data-station="{{ $stationId }}">All</span>&nbsp;|&nbsp;
                                        <span class="control js-invert" data-station="{{ $stationId }}">Invert</span>&nbsp;|&nbsp;
                                        <span class="control js-nothing" data-station="{{ $stationId }}">Nothing</span>
                                    </div>
                                </th>
                            </tr>
                            @foreach($assets as $typeId => $quantity)
                                <tr class="asset" >
                                    <td class='left' colspan="3">
                                        <input type="checkbox"
                                               name="type[]"
                                               value="{{ $stationId . '_' . $typeId }}"
                                               data-station="{{ $stationId }}" />
                                        <img src="https://image.eveonline.com/Type/{{ $typeId }}_32.png" alt="" />
                                        {{ $arrAssets['items'][$typeId] }}
                                    </td>
                                    <td class='right' width="10%">
                                        {{ number_format($quantity, 0) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        </tbody>
                    </table>
                    <input type="hidden" name="format" value="json" />
                </form>
                <button class="btnScrollToTop" title="Top"><img src="/images/arrow-up.png" width="50" height="50" alt=""></button>
            </div>
        </div>
    </div>
@endsection

@section('view.scripts')
    <script>
        function updateLabels() {
            var assetsChecked = $('.asset.active').length;
            $('.js-exportTitle').html((assetsChecked > 0 && assetsChecked < $('.asset').length) ? 'Selected' : 'All');
            $('.js-checkedTotal').html(assetsChecked);
        }
        $(document).ready(function(){
            $('.js-format').on('click', function(){
                $('[name="format"]')
                    .val($(this).data('format'))
                    .closest('form').submit();
            });
            $('.asset').on('click', function(){
                $(this).toggleClass('active');

                var cb = $(this).find('[name="type[]"]');
                cb.prop('checked', !cb.prop('checked'));

                updateLabels();
            });

            $('.js-all').on('click', function() {
                $('input[data-station="' + $(this).data('station') + '"]')
                    .prop('checked', true)
                    .closest('tr').addClass('active');

                updateLabels();
            });
            $('.js-invert').on('click', function() {
                $('input[data-station="' + $(this).data('station') + '"]').each(function() {
                    $(this).prop('checked', !$(this).prop('checked'))
                        .closest('tr').toggleClass('active');
                });

                updateLabels();
            });
            $('.js-nothing').on('click', function() {
                $('input[data-station="' + $(this).data('station') + '"]')
                    .prop('checked', false)
                    .closest('tr').removeClass('active');

                updateLabels();
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