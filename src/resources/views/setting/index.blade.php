@extends('web::layouts.grids.4-4-4')

@section('title', trans('calendar::seat.plugin_name') . ' | ' . trans('calendar::seat.settings'))
@section('page_header', trans('calendar::seat.settings'))

@section('left')
    @include('calendar::setting.includes.notifications')
@stop

@section('center')
    @include('calendar::setting.includes.tags')

    @include('calendar::setting.includes.modals.confirm_delete_tag')

    @include('calendar::setting.includes.modals.edit_tag')
@stop

@section('right')
    @include('calendar::setting.includes.discord')
@stop

@push('head')
    <link rel="stylesheet" href="{{ asset('web/css/bootstrap-colorpicker.min.css') }}"/>
    <link rel="stylesheet" href="{{ asset('web/css/calendar.css') }}"/>
@endpush

@push('javascript')
    <script type="text/javascript" src="{{ asset('web/js/bootstrap-colorpicker.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('web/js/settings.js') }}"></script>
    <script type="text/javascript">
        const bgEditPicker = $('#edit_tag_bg_color').colorpicker();
        const fgEditPicker = $('#edit_tag_text_color').colorpicker();

        $('#modalEdit').on('show.bs.modal', function (e) {
            const link = '{{ route('tags.show', 0) }}';
            const modal = $(this);

            modal.find('.overlay').removeClass('d-none').addClass('d-flex');
            modal.find('input[type="text"]').each(function (index, input) {
                $(input).val('');
            });

            $.ajax({
                url: link.replace('/0', '/' + $(e.relatedTarget).attr('data-tag-id')),
                dataType: 'json',
                method: 'GET'
            }).done(function (data) {
                bgEditPicker.colorpicker('setValue', data.bg_color);
                fgEditPicker.colorpicker('setValue', data.text_color);

                modal.find('input[name="name"]').val(data.name);
                modal.find('input[name="order"]').val(data.order);
                modal.find('select[name="analytics"]').val(data.analytics);
                modal.find('input[name="quantifier"]').val(data.quantifier);
                modal.find('input[name="tag_id"]').val(data.id);
                modal.find('input[name="bg_color"]').val(data.bg_color);
                modal.find('input[name="text_color"]').val(data.text_color);

                for (let integration of data.integrations) {
                    modal.find(`option[id="integration-${integration.id}"]`).prop('selected', true);
                }

                modal.find('.overlay').addClass('d-none').removeClass('d-flex');
            });
        });
    </script>
@endpush
