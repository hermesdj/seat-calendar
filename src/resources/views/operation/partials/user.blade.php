@if($op->user)
    @include('web::partials.character', ['character' => $op->user->main_character])
@endif
