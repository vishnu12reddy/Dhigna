<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.stripe_account') @lang('eventmie-pro::em.country')</label>
    <div class="col-md-9">
        <select name="country" class="form-control" id="country">
            <option value="AU" {{$user->country == 'AU' ?  'selected' : ''}} >Australia</option>
            <option value="AT" {{$user->country == 'AT' ?  'selected' : ''}} >Austria</option>
            <option value="BE" {{$user->country == 'BE' ?  'selected' : ''}} >Belgium</option>
            <option value="BR" {{$user->country == 'BR' ?  'selected' : ''}} >Brazil</option>
            <option value="BG" {{$user->country == 'BG' ?  'selected' : ''}} >Bulgaria</option>
            <option value="CA" {{$user->country == 'CA' ?  'selected' : ''}} >Canada</option>
            <option value="CY" {{$user->country == 'CY' ?  'selected' : ''}} >Cyprus</option>
            <option value="CZ" {{$user->country == 'CZ' ?  'selected' : ''}} >Czech Republic</option>
            <option value="DK" {{$user->country == 'DK' ?  'selected' : ''}} >Denmark</option>
            <option value="EE" {{$user->country == 'EE' ?  'selected' : ''}} >Estonia</option>
            <option value="FI" {{$user->country == 'FI' ?  'selected' : ''}} >Finland</option>
            <option value="FR" {{$user->country == 'FR' ?  'selected' : ''}} >France</option>
            <option value="DE" {{$user->country == 'DE' ?  'selected' : ''}} >Germany</option>
            <option value="GR" {{$user->country == 'GR' ?  'selected' : ''}} >Greece</option>
            <option value="HK" {{$user->country == 'HR' ?  'selected' : ''}} >Hong Kong</option>
            <option value="HU" {{$user->country == 'HU' ?  'selected' : ''}} >Hungary</option>
            <option value="IN" {{$user->country == 'IN' ?  'selected' : ''}} >India</option>
            <option value="IE" {{$user->country == 'IE' ?  'selected' : ''}} >Ireland</option>
            <option value="IT" {{$user->country == 'IT' ?  'selected' : ''}} >Italy</option>
            <option value="JP" {{$user->country == 'JP' ?  'selected' : ''}} >Japan</option>
            <option value="LV" {{$user->country == 'LV' ?  'selected' : ''}} >Latvia</option>
            <option value="LT" {{$user->country == 'LT' ?  'selected' : ''}} >Lithuania</option>
            <option value="LU" {{$user->country == 'LU' ?  'selected' : ''}} >Luxembourg</option>
            <option value="MY" {{$user->country == 'MV' ?  'selected' : ''}} >Malaysia</option>
            <option value="MT" {{$user->country == 'MT' ?  'selected' : ''}} >Malta</option>
            <option value="MX" {{$user->country == 'MX' ?  'selected' : ''}} >Mexico</option>
            <option value="NL" {{$user->country == 'NL' ?  'selected' : ''}} >Netherlands</option>
            <option value="NZ" {{$user->country == 'NZ' ?  'selected' : ''}} >New Zealand</option>
            <option value="NO" {{$user->country == 'NO' ?  'selected' : ''}} >Norway</option>
            <option value="PL" {{$user->country == 'PL' ?  'selected' : ''}} >Poland</option>
            <option value="PT" {{$user->country == 'PT' ?  'selected' : ''}} >Portugal</option>
            <option value="RO" {{$user->country == 'RO' ?  'selected' : ''}} >Romania</option>
            <option value="SG" {{$user->country == 'SG' ?  'selected' : ''}} >Singapore</option>
            <option value="SK" {{$user->country == 'SK' ?  'selected' : ''}} >Slovakia</option>
            <option value="SI" {{$user->country == 'SI' ?  'selected' : ''}} >Slovenia</option>
            <option value="ES" {{$user->country == 'ES' ?  'selected' : ''}} >Spain</option>
            <option value="SE" {{$user->country == 'SE' ?  'selected' : ''}} >Sweden</option>
            <option value="CH" {{$user->country == 'CH' ?  'selected' : ''}} >Switzerland</option>
            <option value="AE" {{$user->country == 'AE' ?  'selected' : ''}} >United Arab Emirates</option>
            <option value="GB" {{$user->country == 'GB' ?  'selected' : ''}} >United Kingdom</option>
            <option value="US" {{$user->country == 'US' ?  'selected' : ''}} >United States</option>
            <option value="GI" {{$user->country == 'GI' ?  'selected' : ''}} >Gibraltar</option>
            <option value="ID" {{$user->country == 'ID' ?  'selected' : ''}} >Indonesia</option>
            <option value="LI" {{$user->country == 'LI' ?  'selected' : ''}} >Liechtenstein</option>
            <option value="PH" {{$user->country == 'PH' ?  'selected' : ''}} >Philippines</option>
            <option value="TH" {{$user->country == 'TH' ?  'selected' : ''}} >Thailand</option>
        </select>
    </div>
</div>
<br>
@if(empty(\Auth::user()->stripe_account_id))
<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.stripe_account')</label>
    <div class="col-md-9">
        <button type="button" id="stripe_button" class="lgx-btn lgx-btn-black btn-lg"><i class="fab fa-stripe"></i> @lang('eventmie-pro::em.connect_stripe')</button>
    </div>
</div>

@else
<div class="form-group row">
    <label class="col-md-3">@lang('eventmie-pro::em.stripe_account')</label>
    <div class="col-md-9">
        <button type="button" class="btn btn-success btn-lg disabled" disabled><i class="fab fa-stripe"></i> Stripe Connected <i class="fas fa-check-circle"></i></button>
        <p class="help-block">NOTE: Your Stripe account must have at least one of the following capabilities enabled: <strong>transfers</strong> or <strong>legacy_payments</strong> to start receiving payouts. <a target="_blank" href="https://stripe.com/docs/connect/required-verification-information#US-full-individual--card_payments|transfers">Visit here for more info</a></p>
    </div>
</div>
@endif
<br>

<script type="text/javascript">

function submitForm(e) {
    e.preventDefault();
    console.log(e);
}
$(document).ready(function (e) {

    $('#stripe_button').click(function(){
        var country = document.getElementById("country").value;
        console.log(country);
        
        window.location.href = route('connect_stripe', {'country' : country});
    
    });
        
       
    
    
});
     
</script>