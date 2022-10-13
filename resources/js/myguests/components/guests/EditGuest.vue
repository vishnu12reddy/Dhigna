<template>
    <div>
        <div class="modal modal-mask" v-if="edit_guest > 0">
            <div class="modal-dialog modal-container">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                        <h3 class="title">{{ trans('em.guest') }} {{ trans('em.details') }}</h3>
                    </div>
                    
                    <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="guest_id" :value="guest.id">
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

                        </div>

                        <div class="modal-footer">
                            <button type="submit" :class="{ 'disabled' : disable }"  :disabled="disable" class="btn lgx-btn btn-block"><i class="fas fa-cash-register"></i> {{ trans('em.edit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</template>

<script>

import Vue from "vue";
import mixinsFilters from '../../../mixins.js';

import VeeValidate from "vee-validate";
Vue.use(VeeValidate);


export default {
    props: ["edit_guest", "guest"],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            name        : '',
            email       : '',
            disable   : false
             
        }
    },

    methods: {
        // reset form and close modal
        close: function () {    
            this.$parent.edit_guest    = 0;
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
            let post_url = route('edit_guest');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {

                if(res.data.status){

                    this.showNotification('success',  trans('em.guest')+' '+trans('em.saved')+' '+trans('em.successfully'));
                            
                    Swal.hideLoading();
                    _this.disable    = false;
                
                    // on success
                    // use vuex to update global sponsors array
                    var promise = new Promise(function(resolve, reject) { 
                            Swal.hideLoading();
                            _this.disable    = false;
                        
                            resolve(); 
                        
                        }); 
                        
                    promise. 
                        then(function () { 
                                     
                            _this.$parent.edit_guest = 0;
                            _this.$parent.getMyGuests();
                            _this.$parent.edit_guest_data = [];
                            
                            
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

        //editGuest

        editGuest(){
            this.name  = this.guest.name;
            this.email = this.guest.email;
        }

    },

    mounted(){
        this.editGuest();
    },

    

}
</script>