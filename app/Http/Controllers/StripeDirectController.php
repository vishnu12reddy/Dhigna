<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Cashier\Exceptions\IncompletePayment;
use App\Http\Controllers\Eventmie\BookingsController as BaseBookingsController;
use Classiebit\Eventmie\Models\Commission;
use Auth;

class StripeDirectController extends BaseBookingsController
{
    /** 
     *  stripe response
     */
    public function stripeResponse(Request $request)
    {
        $booking_data       = session('booking');

        if(empty($booking_data))
            return redirect()->back()->withErrors([__('eventmie-pro::em.booking').' '.__('eventmie-pro::em.failed')]);

        // organizer stripe connected account id
        $stripe_account_id  = User::find($booking_data[key($booking_data)]['organiser_id'])->stripe_account_id;
                
        $flag           = [];
        
        try
        {
            //create stripe 
            \Stripe\Stripe::setApiKey(setting('apps.stripe_secret_key'));

            // get checkout session
            $checkout = \Stripe\Checkout\Session::retrieve(
                session('checkout_id'),
                ['stripe_account' => $stripe_account_id]
            );
            
            //get payment
            $stripe = \Stripe\PaymentIntent::retrieve($checkout->payment_intent,
                ['stripe_account' => $stripe_account_id]
            );

            //check payment success or not
            if($stripe->status == 'succeeded')
            {   
                // set data
                if($stripe->charges['data'][0]->paid)
                {
                    $flag['status']             = true;
                    $flag['transaction_id']     = $stripe->charges['data'][0]->balance_transaction; // transation_id
                    $flag['payer_reference']    = $stripe->charges['data'][0]->id;                  // charge_id
                    $flag['message']            = $stripe->charges['data'][0]->outcome['seller_message']; // outcome message
                }
                else
                {   
                    $flag['status']             = false;
                    $flag['error']              = $stripe->charges['data'][0]->failure_message;
                }
            }
            else
            {
                $flag = [
                    'status'    => false,
                    'error'     => $stripe->status,
                ];
            }    

        } 

        // Laravel Cashier Incomplete Exception Handling for 3D Secure / SCA -> 4000000000003220 error card number
        catch (IncompletePayment $ex) {
            
            $redirect_url = route(
                'cashier.payment',
                [$ex->payment->id, 'redirect' => route('chekcout_after3DAuthentication',['id' => $ex->payment->id ])]
            ); 

            return response()->json(['url' => $redirect_url, 'status' => true]);
        }

        // All Exception Handling like error card number
        catch (\Throwable $th)
        {
            // fail case
            $flag = [
                'status'    => false,
                'error'     => $th->getMessage(),
            ];
        }
        
        return $this->finish_checkout($flag);
    } 

    /**
     *   redirect stripe checkout page
     */
    public function redirectStripeCheckout()
    {
        $order         = session('pre_payment');
        $booking_data  = session('booking');
        $commission    = session('commission');
        
        if(empty($order))
            return redirect()->back()->withErrors([__('eventmie-pro::em.booking').' '.__('eventmie-pro::em.failed')]);

        $user = \Auth::user();

        if(!empty(Auth::user()->is_manager))
        {
            $user = User::find(Auth::user()->organizer_id);
            
        }
        
        // create customer
        if( empty($user->stripe_id) ){
            $user->createAsStripeCustomer();
        }
        
        $amount         = $order['price'] * 100;
        $amount         = (int) $amount;
        $event_title    = session('payment_method')['event_title'];
        $currency       = session('payment_method')['currency'];
        $quantity       = 1;

        // organizer stripe connected account id
        $stripe_account_id  = User::find($booking_data[key($booking_data)]['organiser_id'])->stripe_account_id;
        
        //apply tax when user have stripe connected account
        $total_tax = null;
        if(!empty($stripe_account_id))
        {
            $total_tax    = (int)(collect($commission)->sum('admin_commission') + collect($commission)->sum('admin_tax')) * 100;
            
        }
        $checkout           = null;
       
        try
        {
            \Stripe\Stripe::setApiKey(setting('apps.stripe_secret_key'));
            
            $checkout = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                              'name' =>  $event_title,
                            ],
                            'unit_amount' => $amount,
                          ],
                          'quantity' => $quantity,
                    ]
                ],
                'payment_intent_data' => [
                    'application_fee_amount' => $total_tax,
                ],
                'mode' => 'payment',
                'success_url' => route('stripe_response'),
                'cancel_url'  => route('stripe_response'),
        
            ], ['stripe_account' => $stripe_account_id]);
  
        }
        catch (\Throwable $th)
        {
            return redirect()->back()->withErrors([$th->getMessage()]);
        }
        
        session(['checkout_id'=> $checkout->id ]);
        
        return view('stripe.stripe_checkout', [
            'checkout' => $checkout,
            'stripe_account_id' => $stripe_account_id
        ]);
    }

    // after redirect after3DAuthentication 

    public function after3DAuthentication($paymentIntent = null)
    {
        $booking_data       = session('booking');

        if(empty($booking_data))
            return redirect()->back()->withErrors([__('eventmie-pro::em.booking').' '.__('eventmie-pro::em.failed')]);

        // organizer stripe connected account id
        $stripe_account_id  = User::find($booking_data[key($booking_data)]['organiser_id'])->stripe_account_id;
                
        session(['authentication_3d' => 1]);
        
        $user   = \Auth::user();
        $flag   = [];
        
        try
        {
            //create stripe 
            \Stripe\Stripe::setApiKey(setting('apps.stripe_secret_key'));
            
            $stripe = \Stripe\PaymentIntent::retrieve($paymentIntent,
                ['stripe_account' => $stripe_account_id]
            );
    
            // successs 
            if($stripe->status == 'succeeded')
            {   
            
                // set data
                if($stripe->charges['data'][0]->paid)
                {
                    $flag['status']             = true;
                    $flag['transaction_id']     = $stripe->charges['data'][0]->balance_transaction; // transation_id
                    $flag['payer_reference']    = $stripe->charges['data'][0]->id;                  // charge_id
                    $flag['message']            = $stripe->charges['data'][0]->outcome['seller_message']; // outcome message
                }
                else
                {   
                    $flag['status']             = false;
                    $flag['error']              = $stripe->charges['data'][0]->failure_message;
                }
            }
            else
            {
                $flag = [
                    'status'    => false,
                    'error'     => $stripe->status,
                ];
            }
            
        }

        // All Exception Handling like error card number
        catch (\Exception $ex)
        {
            
            // fail case
            $flag = [
                'status'    => false,
                'error'     => $ex->getMessage(),
            ];
        }
        
        return $this->finish_checkout($flag);
    }

    /**
     *  update commissions
     */

    public function transfer($booking_data = [])
    {   
        if(empty(setting('apps.stripe_public_key')) || empty(setting('apps.stripe_secret_key')) || empty(setting('apps.stripe_direct')) )
            return true;

        $stripe_account_id       = User::find($booking_data[key($booking_data)]['organiser_id'])->stripe_account_id;

        if(empty($stripe_account_id))
            return true;

        $booking_ids             = collect($booking_data)->pluck('id')->all();
        
        $commission_ids          = Commission::whereIn('booking_id', $booking_ids)->pluck('id')->all();

        // Success
        // Update commissions transferred = 1
        Commission::whereIn('id', $commission_ids)->update(['transferred'=>1]);
            
        return true;
    }

    /**
     *  check stripe connected account is verified or not
     */
    
    protected function checkStripeAccount($organizer_id = null)
    {
        $stripe_account_id = User::where(['id' => $organizer_id])->first()->stripe_account_id;

        if(empty($stripe_account_id))
            return __('eventmie-pro::em.stripe_account_not_found');

        $stripe = new \Stripe\StripeClient(
            setting('apps.stripe_secret_key')
          );
        
        $stripe_account = $stripe->accounts->retrieve(
            $stripe_account_id,
            []
        );

        if(empty($stripe_account))
            return __('eventmie-pro::em.stripe_account_not_found');

        if(empty($stripe_account->charges_enabled) || empty($stripe_account->payouts_enabled))
        {
            return $stripe_account->requirements->errors[0]->reason;
        }

        
    }
}
