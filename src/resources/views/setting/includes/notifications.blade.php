<div class="card card-info">
    <div class="card-header with-border p-0">
        <h3 class="card-title p-3">
            <i class="fab fa-slack"></i> {{ trans('calendar::notifications.notification_settings') }}
        </h3>
    </div>
    <form class="form-horizontal" method="POST" action="{{ route('setting.notifications.update') }}">
        {{ csrf_field() }}
        <div class="card-body">
            <p class="callout callout-info text-justify">
                {{ trans('calendar::seat.help_general_config') }}
            </p>

            <div class="form-group row">
                <label for="default_known_duration"
                       class="col-sm-8 col-form-label">{{ trans('calendar::seat.default_known_duration') }}</label>
                <div class="col-sm-4">
                    <div class="form-check">
                        @if(setting('kassie.calendar.default_known_duration', true) == 1)
                            <input type="checkbox" name="default_known_duration" class="form-check-input"
                                   id="default_known_duration" value="1" checked/>
                        @else
                            <input type="checkbox" name="default_known_duration" class="form-check-input"
                                   id="default_known_duration" value="1"/>
                        @endif
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="default_op_duration"
                       class="col-sm-8 col-form-label">{{ trans('calendar::seat.default_op_duration') }}</label>
                <div class="col-sm-4">
                    <div class="form-check">
                        <input type="number" step="1" class="form-control" name="default_op_duration"
                               value="{{ setting('kassie.calendar.default_op_duration', true) }}">
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="forbid_multiple_tags"
                       class="col-sm-8 col-form-label">{{ trans('calendar::seat.forbid_multiple_tags') }}</label>
                <div class="col-sm-4">
                    <div class="form-check">
                        @if(setting('kassie.calendar.forbid_multiple_tags', true) == 1)
                            <input type="checkbox" name="forbid_multiple_tags" class="form-check-input"
                                   id="forbid_multiple_tags" value="1" checked/>
                        @else
                            <input type="checkbox" name="forbid_multiple_tags" class="form-check-input"
                                   id="forbid_multiple_tags" value="1"/>
                        @endif
                    </div>
                </div>
            </div>

            <p class="callout callout-info text-justify">
                {!! trans('calendar::seat.help_notify_locale') !!}
            </p>

            <div class="form-group row">
                <label for="notify_locale"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.notify_locale') }}</label>
                <div class="col-sm-9">
                    <select
                            id="notify_locale"
                            name="notify_locale"
                            class="form-control w-100"
                    >
                        @foreach($languages as $language)
                            <option
                                    value="{{ $language['short'] }}"
                                    @if(setting('kassie.calendar.notify_locale', true) == $language['short'])
                                        selected
                                    @endif
                            >
                                {{ $language['full'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <p class="callout callout-info text-justify">
                {!! trans('calendar::seat.help_notify_operation_interval', ['default_interval' => '<code>15,30,60</code>']) !!}
            </p>
            <div class="form-group row">
                <label for="notify_operation_interval"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.ping_intervals') }}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="notify_operation_interval"
                           value="{{ setting('kassie.calendar.notify_operation_interval', true) }}">
                </div>
            </div>

            <p class="callout callout-info text-justify">
                {{ trans('calendar::seat.help_emoji') }}
            </p>

            <div class="form-group row">
                <label for="slack_emoji_importance_full"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.emoji_full') }}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="slack_emoji_importance_full"
                           id="slack_emoji_importance_full"
                           value="{{ setting('kassie.calendar.slack_emoji_importance_full', true) }}">
                </div>
            </div>
            <div class="form-group row">
                <label for="slack_emoji_importance_half"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.emoji_half') }}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="slack_emoji_importance_half"
                           id="slack_emoji_importance_half"
                           value="{{ setting('kassie.calendar.slack_emoji_importance_half', true) }}">
                </div>
            </div>
            <div class="form-group row">
                <label for="slack_emoji_importance_empty"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.emoji_empty') }}</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" name="slack_emoji_importance_empty"
                           id="slack_emoji_importance_empty"
                           value="{{ setting('kassie.calendar.slack_emoji_importance_empty', true) }}">
                </div>
            </div>

        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info float-right">{{ trans('calendar::seat.save') }}</button>
        </div>
    </form>
</div>
