    @extends('eventmie::layouts.app')

{{-- Page title --}}
@section('title')
    @lang('eventmie-pro::em.manage') @lang('eventmie-pro::em.sub_organizers')
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
    
</script>
    
<script type="text/javascript" src="{{ asset('js/sub_organizers.js') }}"></script>
@stop