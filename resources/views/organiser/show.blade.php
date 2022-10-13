@extends('eventmie::layouts.app')

{{-- Page title --}}
@section('title')
    @lang('eventmie-pro::em.organiser'): {{ $organiser_d->name }}
@endsection
   
@section('content')

<main>
    <div class="lgx-post-wrapper">
         <section>
            <div class="container">
                <div class="row">
                    <div class="col-xs-12 col-sm-5 col-md-4">
                        <article>
                            <header>
                                <figure>
                                    @if($organiser_d->avatar)
                                    <img src="/storage/{{ $organiser_d->avatar }}" alt="{{ $organiser_d->name }}" class="img-responsive img-rounded" style="margin-left: auto;margin-right: auto;"/>
                                    @else
                                    <img src="{{ eventmie_asset('img/512x512.jpg') }}" alt="{{ $tag->title }}" class="img-responsive img-rounded" style="margin-left: auto;margin-right: auto;"/>
                                    @endif
                                </figure>
                                <div class="text-area">
                                    <div class="speaker-info">
                                        <h3>{{ $organiser_d->organisation }}</h3>
                                        {{-- <h4 class="subtitle">{{ $organiser_d->organisation }}</h4> --}}
                                    </div>
                                    <ul class="list-inline lgx-social">
                                        @if($organiser_d->org_facebook)
                                        <li><a href="//{{ $organiser_d->org_facebook }}" target="_blank"><i class="fab fa-facebook-f" aria-hidden="true"></i></a></li>
                                        @endif

                                        @if($organiser_d->org_instagram)
                                        <li><a href="//{{ $organiser_d->org_instagram }}" target="_blank"><i class="fab fa-instagram" aria-hidden="true"></i></a></li>
                                        @endif

                                        @if($organiser_d->org_youtube)
                                        <li><a href="//{{ $organiser_d->org_youtube }}" target="_blank"><i class="fab fa-youtube" aria-hidden="true"></i></a></li>
                                        @endif
                                        
                                        @if($organiser_d->org_twitter)
                                        <li><a href="//{{ $organiser_d->org_twitter }}" target="_blank"><i class="fas fa-globe" aria-hidden="true"></i></a></li>
                                        @endif
                                    </ul>
                                </div>
                            </header>
                        </article>
                    </div>
                    <div class="col-xs-12 col-sm-7 col-md-8">
                        <article>
                            <section>{!! nl2br($organiser_d->org_description) !!}</section>
                        </article>
                    </div>
                </div>

                <organiser-event :active_events="{{ json_encode($activeEvents, JSON_HEX_APOS) }}" 
                    :expired_events="{{ json_encode($expiredEvents, JSON_HEX_APOS) }}"
                    :currency="{{ json_encode($currency, JSON_HEX_APOS) }}"
                    :date_format="{{ json_encode([
                        'vue_date_format' => format_js_date(),
                        'vue_time_format' => format_js_time()
                    ], JSON_HEX_APOS) }}"
                ></organiser-event>

            </div>
        </section>
    </div>
</main>
@endsection

@section('javascript')

<script type="text/javascript" src="{{ asset('js/organiser.js') }}"></script>
@stop