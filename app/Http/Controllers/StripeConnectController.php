<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use App\Models\User;
use Classiebit\Eventmie\Models\Commission;
Use Classiebit\Eventmie\Models\Booking;

class StripeConnectController extends Controller
{
    public function __construct()
    {
        $this->middleware(['only_organizer'])->except(['transfer']);

        \Stripe\Stripe::setApiKey(setting('apps.stripe_secret_key'));

    }

    /**
     *  create stripe account
     */
    public function createStripeAccount()
    {
        //if hava stripe account then don't create account
        if(!empty(Auth::user()->stripe_account_id))
            return redirect()->back();

        try
        {
            $account = \Stripe\Account::create([
                'type'         => 'express',
                'country'      => Auth::user()->country,
                'email'        => Auth::user()->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers'     => ['requested' => true],
                ],
            ]);
        } 
        catch(\Throwable $th)
        {
            $err_response[] = $th->getMessage();
            return redirect()->back()->withErrors($err_response);
        }

        if(empty($account->id)) {
            $msg = __('eventmie-pro::em.account_not_created');
            $err_response[] = $msg;

            return redirect($url)->withErrors($err_response);
        }
            
        try
        {
            $account_links = \Stripe\AccountLink::create([
                'account'     => $account->id,
                'refresh_url' => route('response_connect_stripe'),
                'return_url'  => route('response_connect_stripe'),
                'type'        => 'account_onboarding',
            ]);
        } 
        catch(\Throwable $th)
        {
            $err_response[] = $th->getMessage();
            return redirect()->back()->withErrors($err_response);
        }

        session(['stripe_account_id' => $account->id]);
        return  redirect($account_links->url);

    }

    /**
     *  stripe response
     */

    public function stripeAccountResponse()
    {
        $url               = route('eventmie.profile');
        $stripe_account_id = session('stripe_account_id');
        session()->forget(['stripe_account_id']);

        try
        {
            $account           = \Stripe\Account::retrieve($stripe_account_id);
        }
        catch(\Throwable $th)
        {
            $err_response[] = $th->getMessage();
            return redirect($url)->withErrors($err_response);
        }

        if(empty($account))
        {
            $msg = __('eventmie-pro::em.account_not_created');
            
            $err_response[] = $msg;

            return redirect($url)->withErrors($err_response);
        }

        if(!empty($account->details_submitted))
        {
            $user = User::find(Auth::user()->id);

            $user->stripe_account_id = $stripe_account_id;
            $user->save();
            $msg = __('eventmie-pro::em.account_created');
            session()->flash('status', $msg);
            return success_redirect($msg, $url);
        }
        else
        {
            $msg = __('eventmie-pro::em.account_not_created');

            $err_response[] = $msg;

            return redirect($url)->withErrors($err_response);
        }
        

    }

    /**
     *  transfer
     */

    public function transfer($booking_data = [])
    {   
        $stripe_account_id  = User::find($booking_data[key($booking_data)]['organiser_id'])->stripe_account_id;

        if(empty($stripe_account_id))
            return true;

        $order_number            = $booking_data[key($booking_data)]['order_number'];
        
        $booking_ids             = collect($booking_data)->pluck('id')->all();
        
        $commission_ids          = Commission::whereIn('booking_id', $booking_ids)->pluck('id')->all();

        $total_organizer_earning = Commission::whereIn('booking_id', $booking_ids)->sum('organiser_earning');

        try {

            $transfer = \Stripe\Transfer::create([
                "amount"          => $total_organizer_earning * 100,
                "currency"        => $booking_data[key($booking_data)]['currency'],
                "destination"     => $stripe_account_id,
                "transfer_group"  => $order_number,
            ]);

        } catch(\Throwable $th) {
            // Fail
            return true;
        }

        // Success
        // Update commissions transferred = 1
        Commission::whereIn('id', $commission_ids)->update(['transferred'=>1]);
            
        return true;
    }

    

    
}
