@extends('eventmie::layouts.app')
@section('title')
    @lang('eventmie-pro::em.private') @lang('eventmie-pro::em.event')
@endsection

@section('content')
@include('eventmie::layouts.breadcrumb')

<main>
    <div class="lgx-page-wrapper">
        <!--News-->
        <section>
            <div class="container">
                <div class="row">
                    <div class="col-12 offset-sm-2 col-sm-8 offset-lg-3 col-lg-6">
                        <div class="alert alert-info">@lang('eventmie-pro::em.private_event_text')</div>
                    </div>
                </div>

                <div class="row">

                    <div class="col-12 offset-sm-2 col-sm-8 offset-lg-3 col-lg-6">
                        <form method="POST" class="lgx-contactform" action="{{route('verify_event_password')}}">
                            @csrf
                            <input type="hidden" name="event_id" value={{$event->id}}>
                            
                            <div class="form-group">
                                <label for="event_password" class="col-form-label">@lang('eventmie-pro::em.enter') @lang('eventmie-pro::em.event') @lang('eventmie-pro::em.password') </label>
                                <input type="password" name="event_password" class="form-control lgxname" id="event_password" placeholder="@lang('eventmie-pro::em.event') @lang('eventmie-pro::em.password')" required>
                                
                                @if ($errors->has('event_password'))
                                <div class="alert alert-danger">{{ $errors->first('event_password') }}</div>
                                @endif
                            </div>
                            
                            <button type="submit" class="lgx-btn lgxsend lgx-send btn-block"><span><i class="fas fa-door-closed"></i> @lang('eventmie-pro::em.continue')</span></button>
                        </form>
                        
                    </div> <!--//.COL-->
                </div>

            </div><!-- //.CONTAINER -->
        </section>
        <!--News END-->
    </div>
</main>


@endsection