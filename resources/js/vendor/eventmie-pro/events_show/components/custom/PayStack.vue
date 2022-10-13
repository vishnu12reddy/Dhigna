<template>
    <div v-if="pay_stack == 1" >
        <form ref="form" :action="route" method="POST">
            <div class="row" style="margin-bottom:40px;">
                <div class="col-md-8 col-md-offset-2">
                
                <input type="hidden" name="email" :value="email"> 
                <input type="hidden" name="orderID" :value="orderID">
                <input type="hidden" name="amount" :value="amount">
                <input type="hidden" name="quantity" value="1">
              
                <input type="hidden" name="reference" :value="reference"> 

                <input type="hidden" name="key"  :value="key"> 
                <input type="hidden" name="_token" :value="csrf_token"> 
                <p>
                    <button class="btn btn-success btn-lg btn-block" type="submit" value="Pay Now!" ref="submit">
                    <i class="fa fa-plus-circle fa-lg"></i> Pay Now!
                    </button>
                </p>
                </div>
            </div>
        </form>
    </div>
</template>

<script>

import mixinsFilters from '../../../../../mixins.js';

export default {
   mixins:[
        mixinsFilters
    ],


    data() {
        return {
           email     : null,
           orderID   : null,
           amount    : null,
           key       : null,
           reference : null,
           pay_stack : 0,
           route     : null,
           csrf_token : null,
        }
    },

    
    computed: {
        // get global variables
        
    
    },

    
    methods: {
        // update global variables
        

        //paystack payment
        PayStack(booking_data){
            console.log('paystack1');
    
            // prepare form data for post request
            let post_url  = route('eventmie.bookings_book_tickets');
            axios.post(post_url, booking_data)
            .then(res => {
                
                if(res.data.status > 0 && res.data.paystack.paystack > 0) {
                
                    var _this   = this;
                    var promise = new Promise(function(resolve, reject) { 
                        // only set default value once
                        _this.email     = res.data.paystack.payment_method.customer_email,
                        _this.orderID   = res.data.paystack.order.order_number,
                        _this.amount    = res.data.paystack.order.price,
                        _this.reference = res.data.paystack.reference,
                        _this.route     = res.data.paystack.route,
                        _this.key       = res.data.paystack.secretKey,
                        _this.pay_stack = 1,
                        _this.route     = route('payment_paystack'),
                        _this.csrf_token = res.data.paystack.csrf_token,
                        resolve(true);
                    }); 

                    promise 
                    .then(function(successMessage) { 
                            console.log('paystack2');
                            // _this.$refs.form.submit();
                            _this.$refs.submit.click()
                            _this.$parent.close();
                           
                    }, function(errorMessage) { 
                        console.log(errorMessage); 
                    }); 
                    
                }
                
                
                // when admin create booking
                if(res.data.status && typeof(res.data.url) != "undefined" ){
                    
                    setTimeout(() => {
                        window.location.href = res.data.url;    
                    }, 1000);
                    this.showNotification('success', res.data.message);
                
                }

            })
            .catch(error => {
                this.$parent.disable = false;
                let serrors = Vue.helpers.axiosErrors(error);
                if (serrors.length) {
                    
                    this.$parent.serverValidate(serrors);
                    
                }
            });
        },

        
    }    
}
</script>