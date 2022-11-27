<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
<style>
    * {
        padding: 0;
        margin: 0; 
        font-family: 'DejaVu Sans', sans-serif;
    }
    body {
        margin: 0 auto !important;
        padding: 0 !important;
        height: 100% !important;
        width: 100% !important;
        font-size: 14px;
        font-family: 'DejaVu Sans', sans-serif;
    }
    table {
        width: 95%;
        padding: 1px;
        margin: 0 auto !important;
        border-spacing: 0 !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
    }
    table table table {
        table-layout: auto;
    }
    table td {
        padding: 5px;
        font-size: 14px;
        word-wrap: break-word;
    }
    .center {
        text-align: center;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    [dir=rtl] .text-right {
        text-align: left;
    }
    [dir=rtl] .text-left {
        text-align: right;
    }
    p {
        font-size: 18px;
        display: block;
    }
    .title-bar {
        padding: 0 !important;
        border-bottom: 2px solid #2b2b2b;
    }
    .title-bar .s-heading {
        color: #797979;
        font-size: 14px;
        margin: 0 0 5px 0;
    }
    .title-bar .m-heading {
        color: #3c3c3c;
        font-size: 16px;
        margin: 0;
    }
</style>
</head>
<body {!! is_rtl() ? 'dir="rtl"' : '' !!}>
    <!-- when testing  -->
    {{-- <div style="max-width: 680px;margin: 0 auto;"> --}}
    <!-- when generating  -->
    <div>

        <div>
            <table>
                <tr>
                    <td class="title-bar center"> 
                        <table>
                            <tr>
                                <td style="padding: 10px;width: 40%;" class="text-right">
                                    <img src="https://events.dhigna.com/storage/settings/November2022/pdflogo.png" style="width: 64px;">
                                </td>
                                <td style="padding: 10px;width: 60%;" class="text-left">
                                    <p class="m-heading">{{ (setting('site.site_name') ? setting('site.site_name') : config('app.name')) }}</p>
                                    <p class="s-heading">{{ setting('site.site_slogan') }}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 1. Event details -->
        <div>
            <table>
                <tr>
                    <td style="padding: 20px 10px 10px 10px;width: 30%;" class="text-right">
                        <img style="width: 100%;border-radius: 12px;" src="{{ "data:image/png;base64,".base64_encode(file_get_contents(public_path('/storage/'.$event->thumbnail))) }}">
                    </td>
                    <td style="padding: 10px;width: 70%;" class="text-left">
                        <div>
                            <table>
                                <tr>
                                    <td class="text-left">
                                        <img src="{{ "data:image/png;base64,".base64_encode(file_get_contents(public_path('/storage/extras/red-carpet.png'))) }}" style="width: 24px;"> 
                                        <p>{{$event->title}}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left">
                                        <img src="{{ "data:image/png;base64,".base64_encode(file_get_contents(public_path('/storage/extras/location.png'))) }}" style="width: 24px;">
                                        <p>{{ucfirst($event->venue)}} | {{ucfirst($event->address)}}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left">
                                        <img src="{{ "data:image/png;base64,".base64_encode(file_get_contents(public_path('/storage/extras/calendar.png'))) }}" style="width: 24px;">
                                        <p>
                                        @if($booking['event_start_date'] == $booking['event_end_date'])
                                        {{ userTimezone($booking['event_start_date'].' '.$booking['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(true))  }}
                                        @else
                                        {{ userTimezone($booking['event_start_date'].' '.$booking['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(true)) }} -
                                            @if( userTimezone($booking['event_start_date'].' '.$booking['event_start_time'], 'Y-m-d H:i:s', 'Y-m-d') <=  userTimezone($booking['event_end_date'].' '.$booking['event_end_time'], 'Y-m-d H:i:s', 'Y-m-d'))
                                                {{ userTimezone($booking['event_end_date'].' '.$booking['event_end_time'], 'Y-m-d H:i:s', format_carbon_date(true))}}
                                                
                                            @else
                                                {{ userTimezone($booking['event_start_date'].' '.$booking['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(true))}}
                                                    
                                            @endif
                                        @endif

                                        {{ '('.userTimezone($booking['event_start_date'].' '.$booking['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(false)).' - '.userTimezone($booking['event_end_date'].' '.$booking['event_end_time'], 'Y-m-d H:i:s', format_carbon_date(false)).')' }}
                                        
                                        {{ showTimezone() }}
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left">
                                        <img src="{{ "data:image/png;base64,".base64_encode(file_get_contents(public_path('/storage/extras/identity.png'))) }}" style="width: 24px;">
                                        <p>{{ ucfirst($booking['customer_name']) }}</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-left">
                                        <img src="{{ "data:image/png;base64,".base64_encode(file_get_contents(public_path('/storage/extras/ticket.png'))) }}" style="width: 24px;">
                                        <p>{{ $booking['ticket_title'].' '.$booking['ticket_price'].' '.$booking['currency'] }} <small>(x{{$booking['quantity']}})</small></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    
                </tr>
            </table>
        </div>

        <div>
            <table>
                <tr>
                    <td><hr style="border: 1px solid #000;"></td>
                </tr>
            </table>
        </div>
        
        <!-- 2. Booking details -->
        <br>
        <div>
            <table>
                <tr>
                    <td class="center">
                        <p><span style="font-size: 24px;font-weight: 600;">#</span> {{$booking['order_number']}}</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 2. QrCode -->
        <div>
            <table>
                <tr>
                    <td style="text-align: center;padding-top: 10px;">
                        @php $qrcode = $booking['customer_id'].'/'.$booking['id'].'-'.$booking['order_number'].'.png'; @endphp
                        <img src="{{ "data:image/png;base64,".base64_encode(file_get_contents(public_path('/storage/qrcodes/'.$qrcode))) }}" style="width: 50%;">
                    </td>
                </tr>
            </table>
        </div>

        
        {{-- CUSTOM --}}
        <!-- 3. Attendee details -->
        <div style="margin-top: 10px;">
            <table>
                <tr>
                <td colspan="2"><p class="b-heading center">@lang('eventmie-pro::em.attendees')</p></td>
                </tr>
            </table>
        </div>
        <div>
            <table>
                <tr>
                    <td><hr></td>
                </tr>
            </table>
        </div>
        <div>
            <table>
                <thead>
                    <tr>
                        <td><b>#</b></td>
                        <td><b>@lang('eventmie-pro::em.name')</b></td>
                        <td><b>@lang('eventmie-pro::em.phone')</b></td>
                        <td><b>@lang('eventmie-pro::em.address')</b></td>
                        @if(!empty($booking['attendees'][0]['seat']))
                        <td><b>@lang('eventmie-pro::em.seat')</b></td>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking['attendees'] as $key => $attendee)
                    <tr>
                        <td><p>{{$key+1}}</p></td>
                        <td><p><small>{{ucfirst($attendee['name'])}}</small></p></td>
                        <td><p><small>{{ $attendee['phone'] }}</small></p></td>
                        <td><p><small>{{ $attendee['address'] }}</small></p></td>
                        @if(!empty($attendee['seat']))
                        <td><p>{{ $attendee['seat']['name'] }}</p></td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- CUSTOM --}}

    </div>


</body>
</html>