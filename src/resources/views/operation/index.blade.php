@extends('web::layouts.grids.12')
@php use Seat\Services\Settings\Profile; @endphp

@section('title', trans('calendar::seat.plugin_name') . ' | ' . trans('calendar::seat.operations'))
@section('page_header', trans('calendar::seat.all_operations'))

@section('full')

    @if(auth()->user()->can('calendar.create'))
        <div class="row margin-bottom">
            <div class="col-md-offset-8 col-md-4">
                <div class="pull-right">
                    <button type="button" class="btn btn-primary" data-toggle="modal"
                            data-target="#modalCreateOperation">
                        <i class="fas fa-plus"></i>&nbsp;&nbsp;
                        {{ trans('calendar::seat.add_operation') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    @include('calendar::operation.modals.create_operation')
    @include('calendar::operation.modals.update_operation')
    @include('calendar::operation.modals.confirm_delete')
    @include('calendar::operation.modals.confirm_close')
    @include('calendar::operation.modals.confirm_cancel')
    @include('calendar::operation.modals.confirm_activate')
    @include('calendar::operation.modals.subscribe')
    @include('calendar::operation.modals.details')

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @include('calendar::operation.ongoing')
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @include('calendar::operation.incoming')
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @include('calendar::operation.faded')
        </div>
    </div>

@stop

@push('head')
    <link rel="stylesheet" href="{{ asset('web/css/daterangepicker.css') }}"/>
    <link rel="stylesheet" href="{{ asset('web/css/bootstrap-slider.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('web/css/calendar.css') }}"/>
@endpush

@push('javascript')
    <script>
        const seat_calendar = {
            url: {
                create_operation: '{{ route('operation.store') }}',
                update_operation: '{{ route('operation.update') }}',
                characters_lookup: '{{ route('calendar.lookups.characters') }}',
                systems_lookup: '{{ route('calendar.lookups.systems') }}'
            }
        };
    </script>

    <script src="{{ asset('web/js/daterangepicker.js') }}"></script>
    <script src="{{ asset('web/js/bootstrap-slider.min.js') }}"></script>
    <script src="{{ asset('web/js/jquery.autocomplete.min.js') }}"></script>
    <script src="{{ asset('web/js/fullCalendar.global.min.js') }}"></script>
    <script src="{{ asset('web/js/natural.js') }}"></script>
    <script src="{{ asset('web/js/calendar.js') }}"></script>
    @include('web::includes.javascript.id-to-name')
    <script type="text/javascript">
        function getDateWithoutTime(dt) {
            dt.setHours(0, 0, 0, 0);
            return dt;
        }

        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap',
            locale: '{{ Profile::get('language') }}',
            events: '{{ route('operation.data') }}',
            height: 750,
            selectable: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            eventTimeFormat: {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit'
            },
            eventClick: function (info) {
                info.jsEvent.preventDefault();
                $(info.el).attr('data-op-id', info.event.id);
                $('#modalDetails').modal('show', info.el);
            },
            @if(auth()->user()->can('calendar.create'))
            select: function (info) {
                console.log('select', info);
                const createDiv = $('#modalCreateOperation');
                createDiv.attr('data-start', info.start.toISOString());
                createDiv.attr('data-end', info.end.toISOString());
                createDiv.attr('data-all-day', info.allDay);
                createDiv.modal('show');

            },
            selectAllow: function (info) {
                return getDateWithoutTime(info.start) >= getDateWithoutTime(new Date());
            },
            @endif
            eventDisplay: 'block',
            eventClassNames: 'calendar-event'
        });

        calendar.render();

        const createModal = $('#modalCreateOperation');

        createModal.on('show.bs.modal', function (e) {
            const now = moment.utc();
            let initialDate = moment.utc().seconds(0).add('5', 'minutes');
            let initialEndDate = null;
            if (e.relatedTarget && e.relatedTarget.dataset && e.relatedTarget.dataset.date) {
                initialDate = moment.utc(e.relatedTarget.dataset.date, 'YYYY-MM-DD');
                initialDate.hours(now.hours());
                initialDate.minutes(now.minutes());
                initialDate.seconds(0);
            } else if (e.target && e.target.dataset && e.target.dataset.start) {
                const {allDay, start, end} = e.target.dataset;
                initialDate = moment.utc(start);
                if (allDay === "true") {
                    initialDate.hours(now.hours());
                    initialDate.minutes(now.minutes());
                    initialDate.seconds(0);
                }

                initialEndDate = moment.utc(end);
                if (initialEndDate.isValid()) {
                    if (allDay === "true") {
                        initialEndDate.hours(now.hours());
                        initialEndDate.minutes(now.minutes());
                        initialEndDate.seconds(0);
                    }
                } else {
                    initialEndDate = null;
                }
            }

            let roundedStartDate = initialDate;
            let endDate = initialEndDate || roundedStartDate.clone().add('3', 'h');

            const options = {
                timePicker: true,
                timePickerIncrement: 5,
                timePicker24Hour: true,
                startDate: roundedStartDate,
                locale: {
                    "format": "MM/DD/YYYY HH:mm"
                },
                parentEl: '#modalCreateOperation'
            };

            options.singleDatePicker = true;
            op_modals.create.find('input[name="time_start"]').daterangepicker(options);
            options.singleDatePicker = false;
            options.endDate = endDate;
            op_modals.create.find('input[name="time_start_end"]').daterangepicker(options);

            if ($('#sliderImportance').length <= 0)
                $('#modalCreateOperation').find('input[name="importance"]').slider({
                    formatter: function (value) {
                        return value;
                    }
                });
        });

        createModal.find('input[name="known_duration"]')
            .on('change', function () {
                op_modals.create.find('.datepicker').toggleClass("d-none");
            });

        $('#calendar-ongoing').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('operation.ongoing') }}'
            },
            dom: 'rt<"col-sm-5"i><"col-sm-7"p>',
            columns: [
                {data: 'title', name: 'title'},
                {data: 'username', name: 'username', orderable: false},
                {data: 'tags', name: 'tags', orderable: false},
                {data: 'importance', name: 'importance'},
                {data: 'start_at', name: 'start_at'},
                {data: 'end_at', name: 'end_at'},
                {data: 'fleet_commander', name: 'fleet_commander', orderable: false},
                    @if(\Seat\Kassie\Calendar\Helpers\SeatFittingPluginHelper::pluginIsAvailable())
                {
                    data: 'doctrine', name: 'doctrine', orderable: false
                },
                    @endif
                {
                    data: 'staging_sys', name: 'staging_sys'
                },
                {data: 'subscription', name: 'subscription', orderable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ],
            order: [
                [4, 'desc']
            ],
            drawCallback: function () {
                // enable tooltip
                $('[data-toggle="tooltip"]').tooltip();

                // resolve EVE ids to names.
                ids_to_names();
            }
        });

        $('#calendar-incoming').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('operation.incoming') }}'
            },
            dom: 'rt<"col-sm-5"i><"col-sm-7"p>',
            columns: [
                {data: 'title', name: 'title'},
                {data: 'username', name: 'username', orderable: false},
                {data: 'tags', name: 'tags', orderable: false},
                {data: 'importance', name: 'importance'},
                {data: 'start_at', name: 'start_at'},
                {data: 'duration', name: 'duration', orderable: false},
                {data: 'fleet_commander', name: 'fleet_commander', orderable: false},
                    @if(\Seat\Kassie\Calendar\Helpers\SeatFittingPluginHelper::pluginIsAvailable())
                {
                    data: 'doctrine', name: 'doctrine', orderable: false
                },
                    @endif
                {
                    data: 'staging_sys', name: 'staging_sys'
                },
                {data: 'subscription', name: 'subscription', orderable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ],
            order: [
                [3, 'asc']
            ],
            drawCallback: function () {
                // enable tooltip
                $('[data-toggle="tooltip"]').tooltip();

                // resolve EVE ids to names.
                ids_to_names();
            }
        });

        $('#calendar-faded').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('operation.faded') }}'
            },
            dom: 'rt<"col-sm-5"i><"col-sm-7"p>',
            columns: [
                {data: 'title', name: 'title'},
                {data: 'username', name: 'username', orderable: false},
                {data: 'tags', name: 'tags', orderable: false},
                {data: 'importance', name: 'importance'},
                {data: 'start_at', name: 'start_at'},
                {data: 'end_at', name: 'end_at'},
                {data: 'fleet_commander', name: 'fleet_commander', orderable: false},
                    @if(\Seat\Kassie\Calendar\Helpers\SeatFittingPluginHelper::pluginIsAvailable())
                {
                    data: 'doctrine', name: 'doctrine', orderable: false
                },
                    @endif
                {
                    data: 'staging_sys', name: 'staging_sys'
                },
                {data: 'subscription', name: 'subscription', orderable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false}
            ],
            order: [
                [4, 'desc']
            ],
            drawCallback: function () {
                // enable tooltip
                $('[data-toggle="tooltip"]').tooltip();

                // resolve EVE ids to names.
                ids_to_names();
            }
        });

        $('#modalDetails')
            .on('show.bs.modal', function (e) {
                let link = '{{ route('operation.detail', 0) }}';

                // load detail content dynamically
                $(this).find('.modal-body')
                    .html('<div class="overlay dark d-flex justify-content-center align-items-center"><i class="fas fa-2x fa-sync-alt fa-spin"></i></div> Loading...')
                    .load(link.replace(/0$/gi, $(e.relatedTarget).attr('data-op-id')), "", function () {
                        // attach the datatable to the loaded modal
                        let attendees_table = $('#attendees');
                        let confirmed_table = $('#confirmed');

                        if (!$.fn.DataTable.isDataTable(attendees_table)) {
                            attendees_table.DataTable({
                                "ajax": "/calendar/lookup/attendees?id=" + $(e.relatedTarget).attr('data-op-id'),
                                "ordering": true,
                                "info": false,
                                "paging": true,
                                "processing": true,
                                "order": [[1, "asc"]],
                                "aoColumnDefs": [
                                    {orderable: false, targets: "no-sort"}
                                ],
                                "columns": [
                                    {data: '_character'},
                                    {data: '_status'},
                                    {data: '_comment'},
                                    {data: '_timestamps'}
                                ],
                                createdRow: function (row, data, dataIndex) {
                                    $(row).find('td:eq(0)').attr('data-order', data._character_name);
                                    $(row).find('td:eq(0)').attr('data-search', data._character_name);
                                }
                            });
                        }

                        if (!$.fn.DataTable.isDataTable(confirmed_table)) {
                            confirmed_table.DataTable({
                                "ajax": "/calendar/lookup/confirmed?id=" + $(e.relatedTarget).attr('data-op-id'),
                                "ordering": true,
                                "info": false,
                                "paging": true,
                                "processing": true,
                                "order": [[1, "asc"]],
                                "aoColumnsDefs": [
                                    {orderable: false, targets: "no-sort"}
                                ],
                                'fnDrawCallback': function () {
                                    $(document).ready(function () {
                                        ids_to_names();
                                    });
                                },
                                "columns": [
                                    {data: 'character.character_id'},
                                    {data: 'character.corporation_id'},
                                    {data: 'type.typeID'},
                                    {data: 'type.group.groupName'}
                                ],
                                createdRow: function (row, data, dataIndex) {
                                    $(row).find('td:eq(0)').attr('data-order', data.character.character_id);
                                    $(row).find('td:eq(0)').attr('data-search', data.character.character_id);
                                }
                            });
                        }
                    });
            })
            .on('hidden.bs.modal', function (e) {
                $(this).find('#attendees').DataTable().destroy();
                $(this).find('#confirmed').DataTable().destroy();
            });

        // direct link
        @if(request()->route()->hasParameter('id'))
        let dl = $('<i>');
        dl.attr('data-op-id', {{ request()->route()->parameter('id') }});
        dl.attr('data-toggle', 'modal');
        dl.attr('data-target', '#modalDetails');

        $('body').find('.wrapper').append(dl);

        dl.click();

        dl.remove();
        @endif
    </script>
@endpush
