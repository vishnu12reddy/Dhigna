{{-- CUSTOM --}}
@if(Auth::user()->hasRole('organiser'))
<hr>
    <h4>@lang('eventmie-pro::em.seller_info')</h4>
<hr>


<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.seller_name')</label>
    <div class="col-md-9">
        <input class="form-control" name="seller_name" type="text" value="{{old('seller_name', $user->seller_name)}}">
        @if ($errors->has('seller_name'))
            <div class="error">{{ $errors->first('seller_name') }}</div>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.seller_info')</label>
    <div class="col-md-9">
        <textarea class="form-control" name="seller_info">{{old('seller_info', $user->seller_info)}}</textarea>
        @if ($errors->has('seller_info'))
            <div class="error">{{ $errors->first('seller_info') }}</div>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.seller_tax_info')</label>
    <div class="col-md-9">
        <textarea class="form-control" name="seller_tax_info" type="text" placeholder="">{{old('seller_tax_info', $user->seller_tax_info)}}</textarea>
        @if ($errors->has('seller_tax_info'))
            <div class="error">{{ $errors->first('seller_tax_info') }}</div>
        @endif
    </div>
</div>

<div class="col-md-12 mb-5 text-center">
    <img id="preview-image-signature" src="{{ asset('storage/'.$user->seller_signature)}}"
        alt="profile-pic" style="max-height: 128px;border-radius: 50%;">
</div>
<div class="form-group row mt-5">
    <label class="col-md-3">@lang('eventmie-pro::em.seller_signature')*</label>
    <div class="col-md-9">
        <input class="form-control" id="seller_signature" name="seller_signature" type="file">
        
        @if ($errors->has('seller_signature'))
            <div class="error">{{ $errors->first('seller_signature') }}</div>
        @endif
    </div>
</div>


<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.seller_note')</label>
    <div class="col-md-9">
        <textarea class="form-control" name="seller_note" type="text"  value="{{$user->seller_note}}" placeholder="">{{old('seller_note', $user->seller_note)}}</textarea>
        @if ($errors->has('seller_note'))
            <div class="error">{{ $errors->first('seller_note') }}</div>
        @endif
    </div>
</div>



@endif
{{-- CUSTOM --}}