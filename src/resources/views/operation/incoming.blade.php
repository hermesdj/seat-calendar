<div class="card">
    <div class="card-header bg-gradient-info">
        <h3 class="mb-0">
            <i class="fas fa-question-circle"></i>
            {{ trans('calendar::seat.incoming_operations') }}
        </h3>
    </div>
    <div class="card-body p-0 m-0">
        <table class="table table-striped table-hover mt-0" id="calendar-incoming" style="margin-top: 0 !important;">
            <thead class="bg-info">
            <tr>
                <th>{{ trans('calendar::seat.title') }}</th>
                <th class="hidden-xs">{{ trans('calendar::seat.tags') }}</th>
                <th>{{ trans('calendar::seat.importance') }}</th>
                <th>{{ trans('calendar::seat.starts_in') }}</th>
                <th class="hidden-xs">{{ trans('calendar::seat.duration') }}</th>
                <th class="hidden-xs">{{ trans('calendar::seat.fleet_commander') }}</th>
                @if(\Seat\Kassie\Calendar\Helpers\SeatFittingPluginHelper::pluginIsAvailable())
                    <th class="hidden-xs">{{ trans('calendar::seat.doctrines') }}</th>
                @endif
                <th>{{ trans('calendar::seat.staging') }}</th>
                <th class="hidden-portrait-xs">{{ trans('calendar::seat.subscription') }}</th>
                <th class="hidden-xs"></th>
            </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
