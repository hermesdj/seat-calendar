@php use Seat\Services\Settings\Profile; @endphp

@if($timestamp)
    <span id="{{ $id }}" data-toggle="tooltip" title=""></span>

    <script type="text/javascript">
        const time = moment.unix({{ $timestamp }});
        const span = document.getElementById('{{ $id }}');

        span.innerHTML = time.locale('{{ Profile::get('language') }}').local().format('LLL');
        span.title = time.locale('{{ Profile::get('language') }}').utc().format('LLL') + ' EVE';
    </script>
@else
    <span>-</span>
@endif