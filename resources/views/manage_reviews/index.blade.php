@extends('eventmie::layouts.app')

@section('title')
    @lang('eventmie-pro::em.manage') @lang('eventmie-pro::em.review')
@endsection

@section('content')

<main>
    <div class="lgx-post-wrapper">
        <section>
            <router-view :events="{{ json_encode($events, JSON_HEX_APOS) }}" :is_admin="{{ json_encode($is_admin, JSON_HEX_APOS) }}" ></router-view>
        </section>
    </div>
</main>
         
@endsection


@section('javascript')
<script type="text/javascript" src="{{ asset('js/manage_reviews.js') }}"></script>
@stop