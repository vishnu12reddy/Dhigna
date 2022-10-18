<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title></title>
    <!--[if mso]>
  <style>
    table {border-collapse:collapse;border-spacing:0;border:none;margin:0;}
    div, td {padding:0;}
    div {margin:0 !important;}
	</style>
  <noscript>
    <xml>
      <o:OfficeDocumentSettings>
        <o:PixelsPerInch>96</o:PixelsPerInch>
      </o:OfficeDocumentSettings>
    </xml>
  </noscript>
  <![endif]-->
    <style>
        table,
        td,
        div,
        h1,
        p {
            font-family: Arial, sans-serif;
        }

        a {
            text-decoration: none;
            color: #00bcd4;
        }

        @media screen and (max-width: 530px) {
            .unsub {
                display: block;
                padding: 8px;
                margin-top: 14px;
                border-radius: 6px;
                background-color: #555555;
                font-weight: bold;
                font-size: 12px;
            }

            .col-lge {
                max-width: 100% !important;
            }
        }

        @media screen and (min-width: 531px) {
            .col-sml {
                max-width: 27% !important;
            }

            .col-lge {
                max-width: 73% !important;
            }
        }

    </style>
</head>

<body style="margin:0;padding:0;word-spacing:normal;background-color:#efefef;">
    <div role="article" aria-roledescription="email" lang="en"
        style="text-size-adjust:100%;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;background-color:#efefef;">
        <table role="presentation" style="width:100%;border:none;border-spacing:0;">
            <tr>
                <td align="center" style="padding:0;">
                    <!--[if mso]>
                <table role="presentation" align="center" style="width:600px;">
                <tr>
                <td>
                <![endif]-->
                    <table role="presentation"
                        style="width:94%;max-width:600px;border:none;border-spacing:0;text-align:left;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                        <tr>
                            <td style="padding:40px 30px 30px 30px;text-align:center;font-size:24px;font-weight:bold;">
                                <a href="{{ eventmie_url() }}" style="text-decoration:none;">
                                    <img
                                        src="{{ url('').'/storage/'.setting('site.logo') }}" width="80" alt="Logo"
                                        style="width:88px;max-width:80%;height:auto;border:none;text-decoration:none;color:#ffffff;">
                                    <h1
                                        style="margin-top:8px;margin-bottom:0px;font-size:26px;font-weight:bold;color: #000;">
                                        {{ (setting('site.site_name') ? setting('site.site_name') : config('app.name')) }}</h1>
                                    <h5
                                        style="margin-top:6px;margin-bottom:8px;font-size:20px;font-weight:normal;color: #222;">
                                        {{ setting('site.site_slogan') }}</h5>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px;background-color:#ffffff;">
                                <h1
                                    style="margin-top:0;margin-bottom:16px;font-size:26px;line-height:32px;font-weight:bold;letter-spacing:-0.02em;">
                                    @lang('eventmie-pro::em.booking_success')</h1>
                                <p style="margin:0;">@lang('eventmie-pro::em.download_ticket_attached')</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <td
                                style="padding:15px 30px 11px 30px;font-size:0;background-color:#ffffff;border-bottom:1px solid #f0f0f5;border-color:rgba(201,201,207,.35);">
                               
                                <div
                                    style="display:inline-block;width:100%;vertical-align:top;padding-bottom:20px;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                                    <table style="width:100%;border:none;border-spacing:0;text-align:left;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                                        <tr>
                                            <td
                                                style="padding: 10px; border-bottom: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                @lang('eventmie-pro::em.event')</td>
                                            <td
                                                style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                {{ $mail['mail_data'][0]['event_title'] }}</td>
                                        </tr>
                                        <tr>
                                            <td
                                                style="padding: 10px; border-bottom: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                @lang('eventmie-pro::em.event_date')</td>
                                            <td
                                                style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                {{ userTimezone($mail['mail_data'][0]['event_start_date'].' '.$mail['mail_data'][0]['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(true))  }} - {{ ( userTimezone($mail['mail_data'][0]['event_start_date'].' '.$mail['mail_data'][0]['event_start_time'], 'Y-m-d H:i:s', 'Y-m-d') <= userTimezone($mail['mail_data'][0]['event_end_date'].' '.$mail['mail_data'][0]['event_end_time'], 'Y-m-d H:i:s', 'Y-m-d') ? userTimezone($mail['mail_data'][0]['event_end_date'].' '.$mail['mail_data'][0]['event_end_time'], 'Y-m-d H:i:s', format_carbon_date(true)) : userTimezone($mail['mail_data'][0]['event_start_date'].' '.$mail['mail_data'][0]['event_start_time'], 'Y-m-d H:i:s', format_carbon_date(true)) ) }} {{ showTimezone() }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td
                                                style="padding: 10px; border-bottom: 1px solid #ededed; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">
                                                @lang('eventmie-pro::em.timings')</td>
                                            <td
                                                style="padding: 10px; border-bottom: 1px solid #ededed; color: #455056;">
                                                {{ $mail['event_end_date'] }}
                                            </td>
                                        </tr>
                                        
                                    </table>
                                </div>
                             
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:15px 30px 11px 30px;font-size:0;background-color:#ffffff;">
                                <div style="display:inline-block;width:100%;vertical-align:top;padding-bottom:20px;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                                    @if(!is_null($mail['transaction']) && $mail['transaction']['payment_gateway'] == 'bank')
                                    {{ @$mail['event']['bank_payment_info'] }} <br>
                                    Please add this payer reference {{ @$mail['transaction']['payer_reference'] }} when you transfer money to above bank account details.
                                @endif
                                </div>
                            </td>
                        </tr>
                        
                        <tr>
                            <td
                                style="padding:15px 30px 11px 30px;font-size:0;background-color:#ffffff;">
                                <!--[if mso]>
                <table role="presentation" width="100%">
                <tr>
                <td style="width:100%;padding-bottom:20px;" valign="top">
                <![endif]-->
                                <div
                                    style="display:inline-block;width:100%;vertical-align:top;padding-bottom:20px;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                                    <table style="width:100%;border:none;border-spacing:0;text-align:left;font-family:Arial,sans-serif;font-size:16px;line-height:22px;color:#363636;">
                                        <tr>
                                            <td colspan="2"
                                                style="padding: 10px; width: 35%; font-weight:500; color:rgba(0,0,0,.64)">@lang('eventmie-pro::em.order_terms')</td>
                                        </tr>
                                    </table>
                                </div>
                                <!--[if mso]>
                </td>
                </tr>
                </table>
                <![endif]-->
                            </td>
                        </tr>

                        <tr>
                            <td
                                style="padding:30px;text-align:center;font-size:16px;background-color:#222;color:#cccccc;">
                                <p style="margin:0;font-size:16px;line-height:30px;"><span>©</span> {{ date('Y') }} 
                                    <a style="color: #fff;text-decoration: underline;" href="{{ eventmie_url() }}">{{ (setting('site.site_name') ? setting('site.site_name') : config('app.name')) }}</a><br>
                                    
                                    @if(!empty(setting('site.site_footer'))) {!! setting('site.site_footer') !!} @endif

                                    <br><a
                                        class="unsub" href="{{ eventmie_url() }}"
                                        style="color:#cccccc;font-size: 10px;">@lang('eventmie-pro::em.unsubscribe')</a></p>
                            </td>
                        </tr>
                    </table>
                    <!--[if mso]>
          </td>
          </tr>
          </table>
          <![endif]-->
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
