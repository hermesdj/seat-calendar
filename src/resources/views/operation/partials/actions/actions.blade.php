@include('calendar::operation.partials.actions.show')

@if(carbon()->now()->lt($op->start_at))
    @if(! $op->is_cancelled)
        @include('calendar::operation.partials.actions.subscribe')
    @endif
@endif

@if(!$op->is_cancelled && carbon()->now()->lt($op->end_at))
    @if(auth()->user()->can('calendar.update_all') || $op->user->id == auth()->user()->id)
        @include('calendar::operation.partials.actions.edit')
    @endif
@endif

@if(!$op->is_cancelled && carbon()->now()->gt($op->start_at) && carbon()->now()->lt($op->end_at))
    @if(auth()->user()->can('calendar.close_all') || $op->user->id == auth()->user()->id)
        @include('calendar::operation.partials.actions.close')
    @endif
@endif

@if(auth()->user()->can('calendar.cancel_all') || $op->user->id == auth()->user()->id)
    @if($op->is_cancelled)
        @include('calendar::operation.partials.actions.enable')
    @else
        @if(carbon()->now()->lt($op->start_at))
            @include('calendar::operation.partials.actions.cancel')
        @endif
    @endif
@endif

@if(auth()->user()->can('calendar.delete_all') || $op->user->id == auth()->user()->id)
    @include('calendar::operation.partials.actions.destroy')
@endif
