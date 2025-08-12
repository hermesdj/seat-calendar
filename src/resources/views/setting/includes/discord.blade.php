<div class="card card-info">
    <div class="card-header with-border p-0">
        <h3 class="card-title p-3">
            <i class="fab fa-slack"></i> {{ trans('calendar::seat.discord_integration') }}
        </h3>
    </div>
    <form class="form-horizontal" method="POST" action="{{ route('setting.discord.update') }}">
        {{ csrf_field() }}
        <div class="card-body">
            <div class="form-group row">
                <label for="discord_integration"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.enabled') }}</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        @if(setting('kassie.calendar.discord_integration', true) == 1)
                            <input type="checkbox" name="discord_integration" class="form-check-input"
                                   id="discord_integration" value="1" checked/>
                        @else
                            <input type="checkbox" name="discord_integration" class="form-check-input"
                                   id="discord_integration" value="1"/>
                        @endif
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="discord_client_id"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.discord_client_id') }}</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input type="text" name="discord_client_id"
                               id="discord_client_id" class="form-control"
                               value="{{setting('kassie.calendar.discord_client_id', true)}}"/>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="discord_client_secret"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.discord_client_secret') }}</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input type="text" name="discord_client_secret"
                               id="discord_client_secret" class="form-control"
                               value="{{setting('kassie.calendar.discord_client_secret', true)}}"/>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="discord_bot_token"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.discord_bot_token') }}</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <input type="text" name="discord_bot_token"
                               id="discord_bot_token" class="form-control"
                               value="{{setting('kassie.calendar.discord_bot_token', true)}}"/>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info float-right">{{ trans('calendar::seat.save') }}</button>
        </div>
    </form>
    <form class="form-horizontal" method="POST" action="{{ route('setting.discord.configure') }}">
        {{ csrf_field() }}
        <div class="card-body">
            <div class="form-group row">
                <label for="discord_allowed_channels"
                       class="col-sm-3 col-form-label">{{ trans('calendar::seat.discord_allowed_channels') }}</label>
                <div class="col-sm-9">
                    <div class="form-check">
                        <select
                                multiple="multiple"
                                name="discord_allowed_channels[]"
                                id="discord_allowed_channels"
                                class="form-control"
                        >
                            @foreach($channels->sortBy('name') as $channel)
                                <option
                                        value="{{$channel->id}}"
                                        @if($channel->selected) selected @endif
                                >
                                    {{ $channel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-info float-right">{{ trans('calendar::seat.save') }}</button>
            </div>
        </div>
    </form>
</div>