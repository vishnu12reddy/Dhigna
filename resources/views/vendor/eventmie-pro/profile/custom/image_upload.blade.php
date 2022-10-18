<div class="col-md-12 mb-5 text-center">
    <img id="preview-image-before-upload" src="{{ asset('storage/'.$user->avatar)}}"
        alt="profile-pic" style="max-height: 128px;border-radius: 50%;">
</div>
<div class="form-group row mt-5">
    <label class="col-md-3">@lang('eventmie-pro::em.avatar')*</label>
    <div class="col-md-9">
        <input class="form-control" id="avatar" name="avatar" type="file">
        
        @if ($errors->has('avatar'))
            <div class="error">{{ $errors->first('avatar') }}</div>
        @endif
    </div>
</div>
