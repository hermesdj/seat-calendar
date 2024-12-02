@extends('web::character.layouts.view', ['viewname' => 'paps'])

@section('title', trans_choice('web::seat.character', 1) . ' ' . trans('calendar::seat.paps'))
@section('page_header', trans_choice('web::seat.character', 1) . ' ' . trans('calendar::seat.paps'))

@inject('request', 'Illuminate\Http\Request')

@section('character_content')
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">{{ trans('calendar::seat.paps') }}</h3>
        </div>
        <div class="card-body">
            <h4>{{ trans('calendar::paps.my_paps_per_month') }}</h4>
            <div class="chart">
                <canvas id="papPerMonth" height="150" width="1000"></canvas>
            </div>
            <h4>{{ trans('calendar::paps.my_paps_per_ship_type') }}</h4>
            <div class="chart">
                <canvas id="papPerType" height="150" width="1000"></canvas>
            </div>
            <h4>{{ trans('calendar::paps.hall_of_fame_header') }}</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="row">
                        <div class="col">
                            <h4 class="float-left">{{ trans('calendar::paps.this_week_header') }}</h4>
                            @if($weeklyRanking->count() > 0)
                                <button
                                        type="button"
                                        class="btn btn-sm btn-secondary float-right"
                                        onclick="exportToCsv('{{ trans('calendar::paps.this_week_header') }}', {{json_encode($weeklyRanking)}})">
                                    CSV
                                </button>
                            @endif
                        </div>
                    </div>
                    <table class="table table-striped" id="weekly-top">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('calendar::paps.character_header') }}</th>
                            <th>{{ trans('calendar::paps.paps_header') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($weeklyRanking->take(10) as $top)
                            <tr data-attr="{{ $top->character_id }}">
                                <td>{{ $loop->iteration }}.</td>
                                <td>
                                    @include('web::partials.character', ['character' => $top->character])
                                </td>
                                <td>{{ $top->qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">{{ trans('calendar::paps.first_week_paps') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if(! $weeklyRanking->where('character_id', $character->character_id)->isEmpty())
                            <tfoot class="hidden">
                            <tr>
                                <td>{{ $weeklyRanking->where('character_id', $character->character_id)->keys()->first() + 1 }}
                                    .
                                </td>
                                <td>
                                    @include('web::partials.character', ['character' => $weeklyRanking->where('character_id', $character->character_id)->first()->character])
                                </td>
                                <td>{{ $weeklyRanking->where('character_id', $character->character_id)->first()->qty }}</td>
                            </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col">
                            <h4 class="float-left">{{ trans('calendar::paps.this_month_header') }}</h4>
                            @if($monthlyRanking->count() > 0)
                                <button
                                        type="button"
                                        class="btn btn-sm btn-secondary float-right"
                                        onclick="exportToCsv('{{ trans('calendar::paps.this_month_header') }}', {{json_encode($monthlyRanking)}})">
                                    CSV
                                </button>
                            @endif
                        </div>
                    </div>
                    <table class="table table-striped" id="monthly-top">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('calendar::paps.character_header') }}</th>
                            <th>{{ trans('calendar::paps.paps_header') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($monthlyRanking->take(10) as $top)
                            <tr data-attr="{{ $top->character_id }}">
                                <td>{{ $loop->iteration }}.</td>
                                <td>
                                    @include('web::partials.character', ['character' => $top->character])
                                </td>
                                <td>{{ $top->qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">{{ trans('calendar::paps.first_month_paps') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if(! $monthlyRanking->where('character_id', $character->character_id)->isEmpty())
                            <tfoot class="hidden">
                            <tr>
                                <td>{{ $monthlyRanking->where('character_id', $character->character_id)->keys()->first() + 1 }}
                                    .
                                </td>
                                <td>
                                    @include('web::partials.character', ['character' => $monthlyRanking->where('character_id', $character->character_id)->first()->character])
                                </td>
                                <td>{{ $monthlyRanking->where('character_id', $character->character_id)->first()->qty }}</td>
                            </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col">
                            <h4 class="float-left">{{ trans('calendar::paps.this_year_header') }}</h4>
                            @if($yearlyRanking->count() > 0)
                                <button
                                        type="button"
                                        class="btn btn-sm btn-secondary float-right"
                                        onclick="exportToCsv('{{ trans('calendar::paps.this_year_header') }}', {{json_encode($yearlyRanking)}})">
                                    CSV
                                </button>
                            @endif
                        </div>
                    </div>
                    <table class="table table-striped" id="yearly-top">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('calendar::paps.character_header') }}</th>
                            <th>{{ trans('calendar::paps.paps_header') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($yearlyRanking->take(10) as $top)
                            <tr data-attr="{{ $top->character_id }}">
                                <td>{{ $loop->iteration }}.</td>
                                <td>
                                    @include('web::partials.character', ['character' => $top->character])
                                </td>
                                <td>{{ $top->qty }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">{{ trans('calendar::paps.first_year_paps') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                        @if(! $yearlyRanking->where('character_id', $character->character_id)->isEmpty())
                            <tfoot class="hidden">
                            <tr>
                                <td>{{ $yearlyRanking->where('character_id', $character->character_id)->keys()->first() + 1 }}
                                    .
                                </td>
                                <td>
                                    @include('web::partials.character', ['character' => $yearlyRanking->where('character_id', $character->character_id)->first()->character])
                                </td>
                                <td>{{ $yearlyRanking->where('character_id', $character->character_id)->first()->qty }}</td>
                            </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('javascript')
    <script type="text/javascript" src="{{ asset('web/js/rainbowvis.js') }}"></script>
    <script type="text/javascript">
        function exportToCsv(type, paps) {
            console.log(type, paps);
            const filename = `Pap export - ${type}`;
            const csv = `Character; Pap\n` + paps.map(pap => [pap.character.name, pap.qty].join('; ')).join('\n');
            const blob = new Blob([csv], {type: 'text/csv'});
            const url = URL.createObjectURL(blob);
            const pom = document.createElement('a');
            pom.href = url;
            pom.setAttribute('download', filename);
            pom.click();
        }

        $(function () {
            let rainbow = new Rainbow();
            let themeColor = rgb2hex($('.nav-pills .nav-link.active').css('backgroundColor'));
            let monthlyData = [];
            let shipTypeData = [];
            let shipTypeLabels = [];
            let shipTypeColors = [];

            // just in case we're on white paper, reverse color
            if (themeColor.substr(4) === rgb2hex($('.card').css('backgroundColor')).substr(4))
                themeColor = '#000000';

            rainbow.setSpectrum('#dddddd', themeColor, '#8e8e8e');
            rainbow.setNumberRange(0, {{ $shipTypePaps->count() }});

            @foreach($monthlyPaps as $pap)
            monthlyData.push({x: "{{ $pap->year }}-{{ $pap->month }}", y: {{ $pap->qty }}});
            @endforeach

            @foreach($shipTypePaps as $pap)
            shipTypeData.push({{ $pap->qty ?: 0 }});
            shipTypeLabels.push("{{ $pap->groupName }}");
            shipTypeColors.push('#' + rainbow.colourAt({{ $loop->index }}));
            @endforeach

            new Chart(document.getElementById('papPerMonth').getContext('2d'), {
                type: 'line',
                data: {
                    datasets: [{
                        label: '# participation',
                        data: monthlyData,
                        borderColor: themeColor
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    scales: {
                        xAxes: [{
                            type: 'time',
                            display: true,
                            time: {
                                unit: 'month',
                                displayFormats: {
                                    month: 'MMM YYYY'
                                }
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Timeline'
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                min: 0,
                                stepSize: 1
                            }
                        }]
                    }
                }
            });

            new Chart(document.getElementById('papPerType').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: shipTypeLabels,
                    datasets: [{
                        label: '# participation',
                        data: shipTypeData,
                        backgroundColor: shipTypeColors
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                min: 0,
                                stepSize: 1
                            }
                        }]
                    }
                }
            });

            let tops = $('#weekly-top, #monthly-top, #yearly-top');

            tops.each(function () {
                let found = false;
                let children = $(this).find('tr');
                children.each(function () {
                    if ($(this).attr('data-attr') === {{ $character->character_id }}) {
                        $(this).addClass('bg-' + getActiveThemeColor() + '-gradient');
                        found = true;
                    }
                });

                if (!found)
                    $(this)
                        .find('tfoot')
                        .removeClass('hidden')
                        .addClass('bg-' + getActiveThemeColor() + '-gradient');
            });

            function getActiveThemeColor() {
                let bodyClass = new RegExp(/skin-([a-z0-9_]+)(-light)?/, 'gi').exec($('body').attr('class'));
                if (bodyClass && bodyClass.length > 0)
                    return bodyClass[1];

                return '';
            }

            function rgb2hex(rgb) {
                try {
                    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
                    return (rgb && rgb.length === 4) ? "#" +
                        ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
                        ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
                        ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
                } catch (e) {
                    return rgb;
                }
            }
        });
    </script>
@endpush
