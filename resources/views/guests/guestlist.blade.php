@extends('eventmie::layouts.app')

{{-- Page title --}}
@section('title')
    @lang('eventmie-pro::em.manage') @lang('eventmie-pro::em.guests')
@endsection

    
@section('content')
<main>
    <div class="lgx-page-wrapper">
        <section>
            <router-view ></router-view>
        </section> 
    </div>
    
</main>
@endsection

@section('javascript')
<script>    
    var path     = {!! json_encode($path, JSON_HEX_TAG) !!};
    var glist_id = {!! !empty($glist_id) ? json_encode($glist_id, JSON_HEX_TAG) : 
                    json_encode(null, JSON_HEX_TAG)  !!};
</script>
    
<script type="text/javascript" src="{{ asset('js/myguests.js') }}"></script>
@stop