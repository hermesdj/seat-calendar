@php use Seat\Services\Settings\Profile; @endphp

@if($timestamp)
<span id="{{ $id }}" data-toggle="tooltip" title=""></span>

<script type="text/javascript">
    const time = moment.unix({{ $timestamp }});
    const duration = moment.duration(time.diff(moment.utc()))

    const span = document.getElementById('{{ $id }}');

    span.innerHTML = duration.locale('{{ Profile::get('language') }}').humanize(true);
    span.title = time.locale('{{ Profile::get('language') }}').local().format('LLL');
</script>
@else
    <span>-</span>
@endif