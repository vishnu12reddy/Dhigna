<?php

namespace App\Service;

use App\Models\Event;
use App\Models\Booking;

/* From the DownloadsController */
class TicketPdfGenerator
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // language change
        $this->event     = new Event;
        $this->booking   = new Booking;

    }
    
    /**
     * Show my booking
     *
     * @return array
     */
    public function generateTicketPdf($id = NULL, $order_number = NULL)
    {
        $id             = (int) $id;
        $order_number   = trim($order_number);
        
        // get the booking
        $booking = $this->booking->get_event_bookings(['id'=>$id, 'order_number'=>$order_number]);
        if(empty($booking))
            return false;
        
        $booking = $booking[0];
        
        // generate QrCode
        $qrcode_data = [
            'id'            => $booking['id'],
            'order_number'  => $booking['order_number'],
        ];
        $this->createQrcode($booking, $qrcode_data);

        // get event data for ticket pdf
        $event      = $this->event->get_event(null, $booking['event_id']);
        $currency   = setting('regional.currency_default');
        
        // generate PDF
        // test PDF
        // $img_path = '';
        // return Eventmie::view('eventmie::tickets.pdf', compact('booking', 'event', 'currency', 'img_path'));
        $img_path = str_replace('https://', 'http://', url(''));

        $pdf_html   = (string) \View::make('vendor.eventmie-pro.tickets.pdf', compact('booking', 'event', 'currency', 'img_path'));

        $pdf_name   = $booking['id'].'-'.$booking['order_number'];
        $this->generatePdf($pdf_html, $pdf_name, $booking);
        
        // download PDF
        $path           = '/storage/ticketpdfs/'.$booking['customer_id'];
        $pdf_file    = public_path().$path.'/'.$booking['id'].'-'.$booking['order_number'].'.pdf';
        if (!\File::exists($pdf_file))
            return false;
        
        return  $pdf_file;
        
    }

    protected function createQrcode($data = [], $qrcode_data = [])
    {
        $path           = '/storage/qrcodes/'.$data['customer_id'];
        // first check if directory exists or not
        if (! \File::exists(public_path().$path)) {
            try {
                \File::makeDirectory(public_path().$path, 0755, true);
            } catch (\Throwable $th) {}
        }
        
        $qrcode_file    = public_path().$path.'/'.$data['id'].'-'.$data['order_number'].'.png';
        
        // only create if not already created
        // if (\File::exists($qrcode_file))
        //     return TRUE;
        
        // generate QrCode
        try {
            \QrCode::format('png')->size(512)->generate(json_encode($qrcode_data), $qrcode_file);
        } catch (\Throwable $th) {

        }
        return TRUE;
    }

    /**
     *  generate pdf
     */
    protected function generatePdf($html = null, $pdf_name = null, $data = [])
    {
        $path           = '/storage/ticketpdfs/'.$data['customer_id'];

        // first check if directory exists or not
        if (! \File::exists(public_path().$path))
            \File::makeDirectory(public_path().$path, 0755, true);

        $pdf_file    = public_path().$path.'/'.$data['id'].'-'.$data['order_number'].'.pdf';
        
        // only create if not already created
        // if (\File::exists($pdf_file))
        //     return TRUE;
            
        // start PDF generation

        // remove white spaces and comments
        $html =  preg_replace('/>\s+</', '><', $html);
        if(empty($html))
            return false;

        $options = [
            'defaultFont' => 'sans-serif',
            'isRemoteEnabled' => TRUE,
            'isJavascriptEnabled' => FALSE,
            'debugKeepTemp' => TRUE,
            'isHtml5ParserEnabled' => TRUE,
            'enable_html5_parser' => TRUE,
        ];
        \PDF::setOptions($options)
        ->loadHTML($html)
        ->setWarnings(false)
        ->setPaper('a4', 'portrait')
        ->save($pdf_file);
        
        return TRUE;
    } 


}
