<template>
    <div>
        <div class="modal modal-mask" v-if="register_modal > 0">
            <div class="modal-dialog modal-container">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                        <h3 class="title">{{ trans('em.customer') }} {{ trans('em.details') }}</h3>
                    </div>
                    
                    <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data" >
                        <div class="modal-body">
                                
                            <div class="form-group">
                                <label>{{ trans('em.name') }}</label>
                                <input type="text" class="form-control"  name="name" v-model="name" v-validate="'required'">
                                <span v-show="errors.has('name')" class="help text-danger">{{ errors.first('name') }}</span>
                            </div>
                            
                            <div class="form-group">
                                <label> {{ trans('em.email') }}</label>
                                <input type="text" class="form-control"  name="email" v-model="email" v-validate="'required|email'">
                                <span v-show="errors.has('email')" class="help text-danger">{{ errors.first('email') }}</span>
                            </div>

                            <div class="form-group">
                                <label>{{ trans('em.password') }}</label>
                                <input type="password" class="form-control"  name="password" v-model="password" v-validate="'required'">
                                <span v-show="errors.has('password')" class="help text-danger">{{ errors.first('password') }}</span>
                            </div>

                            <div class="form-group" >
                                <label> {{ trans('em.phone') }}</label>
                                <input type="text" class="form-control"  name="phone" v-model="phone" v-validate="'required'" v-if="is_twilio > 0">
                                <input type="text" class="form-control"  name="phone" v-model="phone" v-else>
                                <span v-show="errors.has('phone')" class="help text-danger">{{ errors.first('phone') }}</span>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" :class="{ 'disabled' : disable }"  :disabled="disable" class="btn lgx-btn btn-block"><i class="fas fa-cash-register"></i> {{ trans('em.continue') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</template>

<script>


import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';

import VeeValidate from "vee-validate";
Vue.use(VeeValidate);


export default {
    props: ["register_modal"],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            name        : '',
            email       : '',
            is_twilio : is_twilio,
            phone     : '',
            password  : '',
            disable   : false
             
        }
    },

    methods: {
        // reset form and close modal
        close: function () {    
            this.$parent.register_modal    = 0;
        },

        // validate data on form submit
        validateForm(event) {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.formSubmit(event);            
                }
            });
        },

        // show server validation errors
        serverValidate(serrors) {
            this.$validator.validateAll().then((result) => {
                this.$validator.errors.add(serrors);
            });
        },

        // submit form
        formSubmit(event) {
            
            // show loader
            this.showLoaderNotification(trans('em.processing'));

            // prepare form data for post request
            this.disable = true;

            // prepare form data for post request
            let _this    = this;
            let post_url = route('guest.register');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                // on success
                // use vuex to update global sponsors array
                if(res.data.status)
                {
                    
                    Swal.hideLoading();
                    this.disable = false;

                    var promise = new Promise(function(resolve, reject) { 
                        _this.$parent.register_user_id = res.data.user.id;
                        _this.$parent.stripe_secret_key  = res.data.stripe_secret_key;
                        
                        resolve(); 
                    
                    }); 
                    
                    promise. 
                        then(function () { 
                            
                            _this.showNotification('success', trans('em.user')+' '+trans('em.register')+' '+trans('em.successfully'));
                            if(res.data.is_verify_email && !res.data.verify_email){
                                _this.showNotification('success', trans('em.email_info'));

                            }else{
                                _this.showNotification('success', trans('em.user')+' '+trans('em.register')+' '+trans('em.successfully'));
                                setTimeout(function(){ 
                                    _this.$parent.validateForm();
                                }, 1000);

                               
                               
                            }
                            _this.close(); 
                        }). 
                        catch(function (error) { 
                            console.log(error);
                            console.log('Some error has occured'); 
                    }); 

                    
                }    

            })
            .catch(error => {
                
                Swal.hideLoading();
                this.disable = false;

                let serrors = Vue.helpers.axiosErrors(error);
                
                if (serrors.length) {
                    this.serverValidate(serrors);
                }
            });
        },

    },

}
</script>