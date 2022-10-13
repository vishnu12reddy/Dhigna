<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!App::environment('local')) {
            URL::forceScheme('https');
        }
        
        if(Schema::hasTable('settings'))
        {

            Schema::defaultStringLength(191);

            //if have stripe payment gateway then set cashier config file
            if(!empty(setting('apps.stripe_public_key')) && !empty(setting('apps.stripe_secret_key')))
            {   
                config(['cashier.key' => setting('apps.stripe_public_key')]);
                config(['cashier.secret' => setting('apps.stripe_secret_key')]);
            }

            
            config(['paystack.publicKey' => setting('apps.paystack_public_key') ]);
            config(['paystack.secretKey' => setting('apps.paystack_secret_key') ]);
            config(['paystack.paymentUrl' => 'https://api.paystack.co' ]);
            config(['paystack.merchantEmail' => setting('apps.paystack_merchant_email')]);
        }

    }
}
