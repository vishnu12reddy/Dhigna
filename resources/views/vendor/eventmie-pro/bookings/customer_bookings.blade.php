@extends('eventmie::bookings.customer_bookings')

@section('javascript')


<script>    
    var path = {!! json_encode($path, JSON_HEX_TAG) !!};
    var disable_booking_cancellation = {!! json_encode(setting('booking.disable_booking_cancellation'), JSON_HEX_TAG) !!};
    var hide_ticket_download = {!! json_encode(setting('booking.hide_ticket_download'), JSON_HEX_TAG) !!};
    var hide_google_calendar = {!! json_encode(setting('booking.hide_google_calendar'), JSON_HEX_TAG) !!};
</script>

<script type="text/javascript" src="{{ asset('js/bookings_customer_v1.8.js') }}"></script>
@stop