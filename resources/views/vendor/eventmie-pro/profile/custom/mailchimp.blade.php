<hr>
<h4>MailChimp Integration</h4>
<hr>

<div class="form-group row">
    <label class="col-md-3">MailChimp ApiKey</label>
    <div class="col-md-9">
        <input class="form-control" name="mailchimp_apikey" type="text" value="{{$user->mailchimp_apikey}}">
        
        @if ($errors->has('mailchimp_apikey'))
            <div class="error">{{ $errors->first('mailchimp_apikey') }}</div>
        @endif
    </div>
</div>

<div class="form-group row">
    <label class="col-md-3">MailChimp List Id</label>
    <div class="col-md-9">
        <input class="form-control" name="mailchimp_list_id" type="text" value="{{$user->mailchimp_list_id}}">
        
        @if ($errors->has('mailchimp_list_id'))
            <div class="error">{{ $errors->first('mailchimp_list_id') }}</div>
        @endif
    </div>
</div>