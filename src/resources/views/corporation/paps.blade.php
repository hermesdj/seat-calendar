@extends('web::corporation.layouts.view', ['viewname' => 'paps'])

@section('title', trans_choice('web::seat.corporation', 1) . ' ' . trans('calendar::seat.paps'))
@section('page_header', trans_choice('web::seat.corporation', 1) . ' ' . trans('calendar::seat.paps'))

@inject('request', 'Illuminate\Http\Request')

@section('corporation_content')
    <div class="card card-default">
        <div class="card-header">
            <h3 class="card-title">{{ trans('calendar::seat.paps') }}</h3>
        </div>
        <div class="card-body">
            <h3>{{ trans('calendar::paps.stats_header') }}</h3>
            <div class="row">
                <div class="col-sm-4">
                    <div class="input-group input-group-sm" id="yearChartSettings">
                        <div class="form-check mr-3">
                            <input type="checkbox" name="grouped" class="form-check-input"/>
                            <label class="form-check-label">{{ trans('calendar::paps.use_people_group_settings') }}</label>
                        </div>
                        <input type="text" name="year" class="form-control" value="{{ carbon()->year }}"
                               placeholder="year"/>
                        <span class="input-group-append">
                        <button type="button"
                                class="btn btn-info btn-flat">{{ trans('calendar::paps.display_btn') }}</button>
                    </span>
                    </div>
                </div>
                <div class="chart">
                    <canvas id="yearPaps" height="600" width="1200"></canvas>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-5">
                    <div class="input-group input-group-sm" id="monthlyStackedChartSettings">
                        <div class="form-check mr-3">
                            <input type="checkbox" name="grouped" class="form-check-input"/>
                            <label class="form-check-label">{{ trans('calendar::paps.use_people_group_settings') }}</label>
                        </div>
                        <select name="month" class="form-control">
                            @for($i = 1; $i < 13; $i++)
                                <option value="{{ $i }}"
                                        @if($i == carbon()->month)selected="selected"@endif>{{ $i }}</option>
                            @endfor
                        </select>
                        <input type="text" name="year" class="form-control" value="{{ carbon()->year }}"
                               placeholder="year"/>
                        <span class="input-group-append">
                        <button type="button"
                                class="btn btn-info btn-flat">{{ trans('calendar::paps.display_btn') }}</button>
                    </span>
                    </div>
                </div>
                <div class="chart">
                    <canvas id="monthlyStackedChart" width="1200"></canvas>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h3>{{ trans('calendar::paps.ranking_header') }}</h3>
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
                            <table class="table table-striped @if($weeklyRanking->count() > 0) ranking-table @endif">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('calendar::paps.character_header') }}</th>
                                    <th>{{ trans('calendar::paps.paps_header') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($weeklyRanking as $pap)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                        @include('web::partials.character', ['character' => $pap->character])
                                        <td>{{ $pap->qty }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">{{ trans('calendar::paps.no_paps_this_week') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
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
                            <table class="table table-striped @if($monthlyRanking->count() > 0) ranking-table @endif">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('calendar::paps.character_header') }}</th>
                                    <th>{{ trans('calendar::paps.paps_header') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($monthlyRanking as $pap)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @include('web::partials.character', ['character' => $pap->character])
                                        </td>
                                        <td>{{ $pap->qty }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">{{ trans('calendar::paps.no_paps_this_month') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
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
                            <table class="table table-striped @if($yearlyRanking->count() > 0) ranking-table @endif">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ trans('calendar::paps.character_header') }}</th>
                                    <th>{{ trans('calendar::paps.paps_header') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($yearlyRanking as $pap)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            @include('web::partials.character', ['character' => $pap->character])
                                        </td>
                                        <td>{{ $pap->qty }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">{{ trans('calendar::paps.no_paps_this_year') }}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('javascript')
    <script type="text/javascript" src="{{ asset('web/js/rainbowvis.js') }}"></script>
    <script type="text/javascript">
        function exportToCsv(type, paps) {
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
            let yearChart, monthChart;
            let rainbow = new Rainbow();
            let yearChartParameters = $('#yearChartSettings');
            let monthChartParameters = $('#monthlyStackedChartSettings');
            let themeColor = rgb2hex($('.nav-pills .nav-link.active').css('backgroundColor'));

            // just in case we're on white paper, reverse color
            if (themeColor.substr(4) === rgb2hex($('.card').css('backgroundColor')).substr(4))
                themeColor = '#000000';

            let yearChartSettings = {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        type: 'line',
                        label: '% participation',
                        data: [],
                        yAxisID: 'pareto',
                        fill: false
                    }, {
                        type: 'bar',
                        label: '# participation',
                        data: [],
                        backgroundColor: [],
                        yAxisID: 'quantity'
                    }]
                },
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'participation of year'
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: true
                    },
                    scales: {
                        xAxes: [{
                            barThickness: 20
                        }],
                        yAxes: [{
                            id: 'quantity',
                            ticks: {
                                min: 0,
                                stepSize: 1
                            },
                            position: 'left'
                        }, {
                            id: 'pareto',
                            ticks: {
                                min: 0
                            },
                            gridLines: {
                                drawOnChartArea: false
                            },
                            position: 'right'
                        }]
                    }
                }
            };

            let monthChartSettings = {
                type: 'horizontalBar',
                data: {
                    labels: [],
                    datasets: []
                },
                options: {
                    title: {
                        display: true,
                        text: 'stacked participation of the month'
                    },
                    scales: {
                        xAxes: [{
                            stacked: true
                        }],
                        yAxes: [{
                            stacked: true,
                            barThickness: 20
                        }]
                    }
                }
            };

            $('.ranking-table').DataTable({
                'dom': '<"toolbar">frtip',
                'order': [[0, 'asc']]
            });

            yearChartParameters.find('button').on('click', function () {
                $.ajax({
                    url: '{{ route('corporation.ajax.paps.year', request()->route('corporation')) }}',
                    data: {
                        year: yearChartParameters.find('input[type="text"]').val(),
                        grouped: yearChartParameters.find('input[type="checkbox"]').is(':checked') ? 1 : 0
                    },
                    success: function (data) {
                        let pareto = [];

                        if (typeof yearChart !== 'undefined')
                            yearChart.destroy();

                        yearChartSettings.data.labels = [];
                        yearChartSettings.data.datasets[0].data = [];
                        yearChartSettings.data.datasets[1].data = [];
                        yearChartSettings.data.datasets[1].backgroundColor = [];
                        $('#yearPaps')
                            .parent('.chart')
                            .find('p')
                            .remove();

                        if (data.length < 1) {
                            $('#yearPaps')
                                .parent('.chart')
                                .append('<p class="text-danger text-center">There are no data to display</p>');
                            return;
                        }

                        rainbow.setNumberRange(0, data.length);
                        rainbow.setSpectrum('#8e8e8e', themeColor, '#dddddd');

                        $(data).each(function (index, record) {
                            yearChartSettings.data.labels.push((record.name == null) ? 'Unknown' : record.name);
                            yearChartSettings.data.datasets[1].data.push(record.qty);

                            if (pareto.length > 0)
                                pareto.push(pareto[pareto.length - 1] + parseFloat(record.qty));
                            else
                                pareto.push(parseFloat(record.qty));

                            yearChartSettings.data.datasets[1].backgroundColor.push('#' + rainbow.colourAt(index))
                        });

                        $(pareto).each(function (index, value) {
                            yearChartSettings.data.datasets[0].data.push(value / pareto[pareto.length - 1] * 100);
                        });

                        yearChartSettings.options.title.text = 'participation of year ' + yearChartParameters
                            .find('input[type="text"]')
                            .val();

                        if (yearChartParameters.find('input[type="checkbox"]').is(':checked'))
                            yearChartSettings.options.title.text = 'grouped ' + yearChartSettings.options.title.text;

                        yearChart = new Chart(document.getElementById('yearPaps').getContext('2d'), yearChartSettings);
                    }
                });
            });

            monthChartParameters.find('button').on('click', function () {
                $.ajax({
                    url: '{{ route('corporation.ajax.paps.stacked', request()->route('corporation')) }}',
                    data: {
                        year: monthChartParameters.find('input[name="year"]').val(),
                        month: monthChartParameters.find('select[name="month"]').val(),
                        grouped: monthChartParameters.find('input[type="checkbox"]').is(':checked') ? 1 : 0
                    },
                    success: function (data) {

                        let pointFound = false;
                        let seriesFound = false;
                        let datasetLabels = [];
                        let series = [];

                        if (typeof (monthChart) !== 'undefined')
                            monthChart.destroy();

                        monthChartSettings.data.labels = [];
                        monthChartSettings.data.datasets = [];

                        $('#monthlyStackedChart')
                            .parent('.chart')
                            .find('p')
                            .remove();

                        if (data.length < 1) {
                            $('#monthlyStackedChart')
                                .parent('.chart')
                                .append('<p class="text-danger text-center">There are no data to display</p>');
                            return;
                        }

                        $(data).each(function (index, record) {
                            pointFound = false;
                            seriesFound = false;

                            if ($.inArray(record.name, monthChartSettings.data.labels) < 0)
                                monthChartSettings.data.labels.push(record.name);

                            if ($.inArray(record.analytics, datasetLabels) < 0) {
                                datasetLabels.push(record.analytics);
                                monthChartSettings.data.datasets.push({
                                    label: record.analytics,
                                    data: []
                                });
                            }

                            $(series).each(function (index, serie) {
                                if (serie.label === record.name) {
                                    seriesFound = true;

                                    $(serie.points).each(function (index, point) {
                                        if (point.name === record.analytics) {
                                            pointFound = true;
                                            point.value += parseFloat(record.qty);
                                        }
                                    });

                                    if (!pointFound)
                                        serie.points.push({
                                            name: record.analytics,
                                            value: parseFloat(record.qty)
                                        });
                                }
                            });

                            if (!seriesFound)
                                series.push({
                                    label: record.name,
                                    points: [{
                                        name: record.analytics,
                                        value: record.qty
                                    }]
                                });
                        });

                        rainbow.setNumberRange(0, monthChartSettings.data.datasets.length);
                        rainbow.setSpectrum(themeColor, '#dddddd');

                        $(monthChartSettings.data.labels).each(function (labelIndex, label) {
                            pointFound = false;

                            $(monthChartSettings.data.datasets).each(function (datasetIndex, dataset) {
                                dataset.backgroundColor = '#' + rainbow.colourAt(datasetIndex);

                                $(series[labelIndex].points).each(function (pointIndex, point) {
                                    if (point.name === dataset.label) {
                                        pointFound = true;
                                        dataset.data.push(parseFloat(point.value));
                                    }
                                });

                                if (!pointFound)
                                    dataset.data.push(0.0);
                            });
                        });

                        monthChartSettings.options.title.text = 'participation of ' + monthChartParameters
                            .find('select[name="month"]')
                            .val() + '-' + monthChartParameters
                            .find('input[name="year"]')
                            .val();

                        if (monthChartParameters.find('input[type="checkbox"]').is(':checked'))
                            monthChartSettings.options.title.text = 'grouped ' + monthChartSettings.options.title.text;

                        monthChart = new Chart(document.getElementById('monthlyStackedChart').getContext('2d'), monthChartSettings);
                    }
                });
            });

            yearChartParameters.find('button').click();
            monthChartParameters.find('button').click();

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
