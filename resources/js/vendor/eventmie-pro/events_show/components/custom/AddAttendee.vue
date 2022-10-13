<template>
    <div class="row">
        <div class="col-md-12">
        
            <div class="modal modal-mask" v-if="add_attendee > 0">
                <div class="modal-dialog modal-container">
                    <div class="modal-content lgx-modal-box">
                        <div class="modal-header">
                            <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                            <h3 class="title"> {{ trans('em.create')+' '+trans('em.user') }}</h3>
                        </div>
                        
                        <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                            
                            <div class="modal-body">

                                 <div class="form-group">
                                    <label for="name">{{ trans('em.name') }}<sup>*</sup></label>
                                    <input type="text" class="form-control lgxname" v-model="name" name="name" v-validate="'required'">
                                    <span v-show="errors.has('name')" class="help text-danger">{{ errors.first('name') }}</span>
                                </div>

                                 <div class="form-group">
                                    <label for="name">{{ trans('em.email') }}<sup>*</sup></label>
                                    <input type="text" class="form-control lgxname" v-model="email" name="email" v-validate="'required'">
                                    <span v-show="errors.has('email')" class="help text-danger">{{ errors.first('email') }}</span>
                                </div>

                                <div class="form-group" >
                                    <label> {{ trans('em.phone') }}</label>
                                    <input type="text" class="form-control"  name="phone" v-model="phone" v-validate="'required'" v-if="is_twilio > 0">
                                    <input type="text" class="form-control"  name="phone" v-model="phone" v-else>
                                    <span v-show="errors.has('phone')" class="help text-danger">{{ errors.first('phone') }}</span>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" :class="{ 'disabled' : disable }"  :disabled="disable" class="btn lgx-btn"><i class="fas fa-sd-card"></i> {{ trans('em.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>


import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';


export default {


    props: ["add_attendee"],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
           name     : null,
           email    : null,
           phone    : null,
           is_twilio : is_twilio,
           disable   : false
        }
    },


    methods: {

        // reset form and close modal
        close: function () {
            this.$refs.form.reset();
            this.$parent.add_attendee = 0;
            
            
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
            let post_url = route('add_attendee');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                
                this.close();
                this.showNotification('success',  trans('em.user')+' '+trans('em.saved')+' '+trans('em.successfully'));
                
                // CUSTOM
                // reload page   
                // setTimeout(function() {
                //     location.reload(true);
                // }, 1000);
                if(res.data.status){

                    Swal.hideLoading();
                    this.disable = false;

                    this.$parent.customer = res.data.attendee;

                    console.log(this.$parent.options.length);
                    if(this.$parent.options.length > 0)
                        this.$parent.options.push(res.data.attendee); 
                    else    
                        this.$parent.options = res.data.customer_options;     
                }else{
                    this.showNotification('error', res.data.message);
                    
                    Swal.hideLoading();
                    this.disable = false;
                }

                //CUSTOM
            
            })
            .catch(error => {
                let serrors = Vue.helpers.axiosErrors(error);
                if (serrors.length) {
                    this.serverValidate(serrors);
                }
            });
        },

        
    },

    mounted(){
       
    }
}
</script>