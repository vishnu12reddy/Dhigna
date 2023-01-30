<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<!-- Packages CSS -->
<link rel="stylesheet" href="{{ eventmie_asset('css/vendor_v1.8.css') }}">

<!-- Bootstrap RTL CSS only if langauge is RTL -->
@if(is_rtl())
<link rel="stylesheet" href="{{ eventmie_asset('css/bootstrap-rtl.min.css') }}">
@endif



<!-- App CSS -->
<link rel="stylesheet" href="{{ eventmie_asset('css/app_v1.8.css') }}">

<!-- CUSTOM CSS -->
<link rel="stylesheet" href="{{ asset('css/eventmie-custom.css') }}">
<!-- CUSTOM CSS -->

<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "fd0kxa8pb0");
</script>
<script type="text/javascript">

    
// set local timezone 
var local_timezone = {!! json_encode(route('eventmie.local_timezone')) !!};

function setLocalTimezone() {
    
    $.ajax({
        url: local_timezone,
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: {
            local_timezone : Intl.DateTimeFormat().resolvedOptions().timeZone,
            
        },
        dataType: "json",
        success: function (data) {
            console.log(data);
        },
        error: function (error) {
            console.log(`Error ${error}`);
        }
    });
}

setLocalTimezone();
</script>