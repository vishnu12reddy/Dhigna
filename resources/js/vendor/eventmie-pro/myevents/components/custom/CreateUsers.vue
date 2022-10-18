<template>
    <div>
        <div class="modal modal-mask" v-if="organizer_id > 0">
            <div class="modal-dialog modal-container">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                        <h3 class="title">{{ trans('em.create') }} {{ trans('em.sub_organizer') }}</h3>
                    </div>
                    
                    <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                                
                            <input type="hidden" class="form-control lgxname"  name="organiser_id" v-model="organiser_id">
                            

                            <div class="form-group">
                                <label>{{ trans('em.select') }} {{ trans('em.role') }}</label>
                                <select name="role" class="form-control" v-model="role" v-validate="'required'">
                                    <option value="4">{{ trans('em.pos') }}</option>
                                    <option value="5">{{ trans('em.scanner') }}</option>
                                    <option value="6">{{ trans('em.manager') }}</option>
                                </select>
                                <span v-show="errors.has('role')" class="help text-danger">{{ errors.first('role') }}</span>    
                            </div>  
                        
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
                                <label> {{ trans('em.password') }}</label>
                                <input type="password" class="form-control"  name="password" v-model="password" v-validate="'required'">
                                <span v-show="errors.has('password')" class="help text-danger">{{ errors.first('password') }}</span>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn lgx-btn btn-block"><i class="fas fa-sd-card"></i> {{ trans('em.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</template>

<script>

import Vue from "vue";
import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';
import VeeValidate from "vee-validate";
Vue.use(VeeValidate);


export default {
    props: ["organizer_id", "organiser_id"],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            name        : '',
            email       : '',
            password    : '',
            role        : 4,
             
        }
    },

    methods: {
        // reset form and close modal
        close: function () {    
            this.$parent.organizer_id    = 0;
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
            // prepare form data for post request
            let post_url = route('organizer_create_user');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                // on success
                // use vuex to update global sponsors array
                if(res.data.status)
                {
                    this.showNotification('success', trans('em.user')+' '+trans('em.saved')+' '+trans('em.successfully'));
                
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                
                }    

            })
            .catch(error => {
                let serrors = Vue.helpers.axiosErrors(error);
                if (serrors.length) {
                    this.serverValidate(serrors);
                }
            });
        },

    },

}
</script>