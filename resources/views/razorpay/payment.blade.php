
<form method="POST" action="https://api.razorpay.com/v1/checkout/embedded" id="razorpay">
    <input type="hidden" name="name" value="{{ setting('site.site_name') ? setting('site.site_name') : config('app.name') }}">
    <input type="hidden" name="key_id" value="{{$order['RazorPayKeyId']}}">
    <input type="hidden" name="order_id" value="{{$order['order_id']}}">
    <input type="hidden" name="prefill[email]" value="{{$order['email']}}">                                                     
    <input type="hidden" name="callback_url" value="{{$order['callback_url']}}">
    <input type="hidden" name="cancel_url" value="{{$order['callback_url']}}">
    <input type="hidden" name="description" value="{{$order['description'] }}">
    <input type="hidden" name="prefill[contact]" value="9123456780">
</form>

<script>
    window.addEventListener('DOMContentLoaded', (event) => {
        console.log('DOM fully loaded and parsed');
        document.getElementById("razorpay").submit();
    });
</script>