@extends('eventmie::layouts.app')

{{-- Page title --}}
@section('title')
    @lang('eventmie-pro::em.manage_events')
@endsection

    
@section('content')
<main>
    <div class="lgx-page-wrapper">
        <section>
            <router-view :date_format="{{ json_encode([
                'vue_date_format' => format_js_date(),
                'vue_time_format' => format_js_time()
            ], JSON_HEX_APOS) }}"></router-view>
        </section> 
    </div>
</main>
@endsection

@section('javascript')
<script>    
    var path                = {!! json_encode($path, JSON_HEX_TAG) !!};
    //CUSTOM
    var organizer_id        = {!! json_encode($organizer_id, JSON_HEX_TAG) !!};
    var is_admin            = {!! json_encode($is_admin, JSON_HEX_TAG) !!}
    //CUSTOM
</script>
    
<script type="text/javascript" src="{{ asset('js/myevents_v1.8.js') }}"></script>
@stop