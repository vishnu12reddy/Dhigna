{{-- CUSTOM --}}
@if(Auth::user()->hasRole('organiser'))
<hr>
    <h4>@lang('eventmie-pro::em.org_info')</h4>
<hr>


<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.organization') @lang('eventmie-pro::em.name')*</label>
    <div class="col-md-9">
        <input class="form-control" name="organisation" type="text" value="{{$user->organisation}}" required>
        @if ($errors->has('organisation'))
            <div class="error">{{ $errors->first('organisation') }}</div>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.description')</label>
    <div class="col-md-9">
        <textarea class="form-control" name="org_description">{!! $user->org_description !!}</textarea>
        @if ($errors->has('org_description'))
            <div class="error">{{ $errors->first('org_description') }}</div>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3">Facebook</label>
    <div class="col-md-9">
        <input class="form-control" name="org_facebook" type="text"  value="{{$user->org_facebook}}" placeholder="e.g. www.facebook.com/YourPage">
        @if ($errors->has('org_facebook'))
            <div class="error">{{ $errors->first('org_facebook') }}</div>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3">Instagram</label>
    <div class="col-md-9">
        <input class="form-control" name="org_instagram" type="text"  value="{{$user->org_instagram}}" placeholder="e.g. www.instagram.com/YourPage">
        @if ($errors->has('org_instagram'))
            <div class="error">{{ $errors->first('org_instagram') }}</div>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3">YouTube</label>
    <div class="col-md-9">
        <input class="form-control" name="org_youtube" type="text"  value="{{$user->org_youtube}}" placeholder="e.g. www.youtube.com/channel/YourChannel">
        @if ($errors->has('org_youtube'))
            <div class="error">{{ $errors->first('org_youtube') }}</div>
        @endif
    </div>
</div>


<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.website')</label>
    <div class="col-md-9">
        <input class="form-control" name="org_twitter" type="text"  value="{{$user->org_twitter}}" placeholder="e.g. www.yourwebsite.com">
        @if ($errors->has('org_twitter'))
            <div class="error">{{ $errors->first('org_twitter') }}</div>
        @endif
    </div>
</div>

@endif
{{-- CUSTOM --}}