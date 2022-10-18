@extends('eventmie::bookings.organiser_bookings')

@section('javascript')

<script>    
    var path = {!! json_encode($path, JSON_HEX_TAG) !!};
    var hide_ticket_download = {!! json_encode(setting('booking.hide_ticket_download'), JSON_HEX_TAG) !!};
</script>

<script type="text/javascript" src="{{ asset('js/bookings_organiser_v1.8.js') }}"></script>
@stop