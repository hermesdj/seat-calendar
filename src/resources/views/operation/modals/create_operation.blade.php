<div class="modal fade" tabindex="-1" role="dialog" id="modalCreateOperation">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-green">
                <h4 class="modal-title">
                    <i class="fas fa-space-shuttle"></i>
                    {{ trans('calendar::seat.add_operation') }}
                </h4>
            </div>
            <div class="modal-body">
                <div class="modal-errors alert alert-danger d-none">
                    <ul></ul>
                </div>
                <form class="form-horizontal" id="formCreateOperation">
                    {{-- Operation title --}}
                    <div class="form-group row">
                        <label for="title" class="col-sm-3 col-form-label">{{ trans('calendar::seat.title') }}
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="title"
                                   placeholder="{{ trans('calendar::seat.placeholder_title') }}">
                        </div>
                    </div>
                    @if(auth()->user()->cannot('calendar.prevent_op_role_restriction'))
                        {{-- Operation role restriction --}}
                        <div class="form-group row">
                            <label for="create_operation_role"
                                   class="col-sm-3 col-form-label">{{ trans_choice('web::seat.role', 1) }}</label>
                            <div class="col-sm-9">
                                <select name="role_name" id="create_operation_role" style="width: 100%;">
                                    <option value=""></option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->title }}">{{ $role->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    @if(auth()->user()->cannot('calendar.prevent_op_importance'))
                        {{-- Operation importance --}}
                        <div class="form-group row">
                            <label for="importance"
                                   class="col-sm-3 col-form-label">{{ trans('calendar::seat.importance') }}
                                <span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-9">
                                <input type="text" class="slider form-control" value="2" data-slider-min="0"
                                       data-slider-max="5"
                                       data-slider-step="0.5" data-slider-value="2" data-slider-id="sliderImportance"
                                       data-slider-tooltip="show" data-slider-handle="round" name="importance"/>
                            </div>
                        </div>
                    @endif
                    {{-- Operation tags --}}
                    <div class="form-group row">
                        <label class="col-sm-3 col-form-label">{{ trans('calendar::seat.tags') }}</label>
                        <div class="col-sm-9">
                            @foreach($tags->chunk(4) as $tags)
                                <div class="row">
                                    @foreach($tags as $tag)
                                        <div class="col-sm-3">
                                            <div
                                                    @if(setting('kassie.calendar.forbid_multiple_tags', true) == 0)
                                                        class="checkbox"
                                                    @else
                                                        class="radio-inline"
                                                    @endif
                                            >
                                                <label>
                                                    <input
                                                            @if(setting('kassie.calendar.forbid_multiple_tags', true) == 0)
                                                                type="checkbox"
                                                            name="checkbox-{{$tag->id}}"
                                                            @else
                                                                type="radio"
                                                            name="operation_tag"
                                                            @endif
                                                            value="{{$tag->id}}"
                                                    >
                                                    @include('calendar::common.includes.tag', ['tag' => $tag])
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- Operation duration --}}
                    <div class="form-group row">
                        <label for="known_duration"
                               class="col-sm-3 col-form-label">{{ trans('calendar::seat.known_duration') }}</label>
                        <div class="col-sm-9">
                            <label class="radio-inline">
                                <input
                                        type="radio"
                                        name="known_duration"
                                        value="yes"
                                        @if(setting('kassie.calendar.default_known_duration', true) == 1) checked @endif
                                /> {{ trans('calendar::seat.yes') }}
                            </label>
                            <label class="radio-inline">
                                <input
                                        type="radio"
                                        name="known_duration"
                                        value="no"
                                        @if(setting('kassie.calendar.default_known_duration', true) == 0) checked @endif
                                /> {{ trans('calendar::seat.no') }}
                            </label>
                        </div>
                    </div>
                    {{-- Operation starts --}}
                    <div class="form-group row datepicker @if(setting('kassie.calendar.default_known_duration', true) == 1) d-none @endif">
                        <label for="time_start" class="col-sm-3 col-form-label">{{ trans('calendar::seat.starts_at') }}
                            ({{ trans('calendar::seat.eve_time') }})
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="time_start"/>
                        </div>
                    </div>
                    {{-- Operation end --}}
                    <div class="form-group row datepicker @if(setting('kassie.calendar.default_known_duration', true) == 0) d-none @endif">
                        <label for="time_start_end"
                               class="col-sm-3 col-form-label">{{ trans('calendar::seat.duration') }}
                            ({{ trans('calendar::seat.eve_time') }})
                            <span class="text-danger">*</span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="time_start_end" id="time_start_end">
                        </div>
                    </div>
                    {{-- Operation staging system --}}
                    <div class="form-group row">
                        <label for="staging_sys"
                               class="col-sm-3 col-form-label">{{ trans('calendar::seat.staging_sys') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="staging_sys"
                                   placeholder="{{ trans('calendar::seat.placeholder_staging_sys') }}">
                            <input type="hidden" name="staging_sys_id">
                        </div>
                    </div>
                    {{-- Operation staging info --}}
                    <div class="form-group row">
                        <label for="staging_info"
                               class="col-sm-3 col-form-label">{{ trans('calendar::seat.staging_info') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="staging_info" id="staging_info"
                                   placeholder="{{ trans('calendar::seat.placeholder_staging_info') }}">
                        </div>
                    </div>
                    {{-- Operation FC --}}
                    <div class="form-group row">
                        <label for="fc"
                               class="col-sm-3 col-form-label">{{ trans('calendar::seat.fleet_commander') }}</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="fc"
                                   placeholder="{{ trans('calendar::seat.placeholder_fc') }}">
                            <input type="hidden" name="fc_character_id">
                        </div>
                    </div>
                    {{-- Operation Doctrine --}}
                    @if(\Seat\Kassie\Calendar\Helpers\SeatFittingPluginHelper::pluginIsAvailable() && \Seat\Kassie\Calendar\Helpers\SeatFittingPluginHelper::hasDoctrines())
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"
                                   for="doctrine_id">{{ trans('calendar::seat.doctrines') }}</label>
                            <div class="col-sm-9">
                                <select name="doctrine_id" class="form-control" id="doctrine_id">
                                    <option value="" selected>-</option>
                                    @foreach($doctrines as $doctrine)
                                        <option value="{{$doctrine->id}}">{{$doctrine->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    @if($channels->isNotEmpty())
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"
                                   for="discord_voice_channel_id">{{ trans('calendar::seat.voice_channel') }}</label>
                            <div class="col-sm-9">
                                <select name="discord_voice_channel_id" class="form-control" id="channel_id">
                                    <option value="" selected>-</option>
                                    @foreach($channels->sortBy('name') as $channel)
                                        <option value="{{$channel->id}}">{{$channel->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    {{-- Operation description --}}
                    <div class="form-group row">
                        <label for="description"
                               class="col-sm-3 col-form-label">{{ trans('calendar::seat.description') }}</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" name="description" rows="8"
                                      placeholder="{{ trans('calendar::seat.placeholder_description') }}"></textarea>
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-light" data-dismiss="modal">
                        <i class="fas fa-times-circle"></i> {{ trans('calendar::seat.close') }}
                    </button>
                    <button type="button" class="btn btn-success" id="create_operation_submit">
                        <i class="fas fa-check-circle"></i> {{ trans('calendar::seat.create_confirm_button_yes') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('javascript')
    <script type="text/javascript">
        $('#create_operation_role').select2({
            placeholder: "{{ trans('calendar::seat.select_role_filter_placeholder') }}",
            allowClear: true
        });

        $('#create-operation-channel').select2({
            placeholder: "{{ trans('calendar::seat.integration_channel') }}",
            allowClear: true
        });
    </script>
@endpush