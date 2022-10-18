<template>

<div class="row">
        <div class="col-md-12">
        
            <div class="modal modal-mask" v-if="add_guest > 0">
                <div class="modal-dialog modal-container">
                    <div class="modal-content lgx-modal-box">
                        <div class="modal-header">
                            <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                            <h3 class="title"> {{ trans('em.create')+' '+trans('em.guest') }}</h3>
                        </div>
                        
                        <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" class="form-control lgxname" v-model="guest_id" name="guest_id" >
                            <input type="hidden" class="form-control lgxname" v-model="glist_ids" name="glist_ids">
                            
                            <div class="form-group">
                                <label for="name">{{ trans('em.name') }}<sup>*</sup></label>
                                <input type="text" class="form-control lgxname" v-model="name" name="name" v-validate="'required'">
                                <span v-show="errors.has('name')" class="help text-danger">{{ errors.first('name') }}</span>
                            </div>

                            <div class="form-group">
                                <label for="email">{{ trans('em.email') }}<sup>*</sup></label>
                                <input type="text" class="form-control lgxname" v-model="email" name="email" v-validate="'required'">
                                <span v-show="errors.has('email')" class="help text-danger">{{ errors.first('email') }}</span>
                            </div>
                            
                            <div class="form-group">
                                <label>{{ trans('em.add_to_guestlist') }}</label>
                                <multiselect
                                    v-model="tmp_glist_ids" 
                                    :options="glist_options" 
                                    :placeholder="'-- '+trans('em.select')+' '+trans('em.guestlist')+' --'" 
                                    label="text" 
                                    track-by="value" 
                                    :multiple="true"
                                    :close-on-select="false" 
                                    :clear-on-select="false" 
                                    :hide-selected="false" 
                                    :preserve-search="true" 
                                    :preselect-first="false"
                                    :allow-empty="true"
                                    :class="'form-control'"
                                >
                                </multiselect>
                            </div> 

                            <div class="modal-footer">
                                <button type="submit" class="btn lgx-btn"><i class="fas fa-sd-card"></i> {{ trans('em.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>

</template>

<script>

import mixinsFilters from '../../../mixins.js';
import Multiselect from 'vue-multiselect';

export default {

    props : [
        'add_guest'
    ],

    mixins:[
        mixinsFilters
    ],

    components: {
        Multiselect,
        
    },

    data() {
        return {
            name              : null,
            email             : null,
            guest_id          : null,

            glist             : [],
            glist_ids         : [],
            glist_options     : [],
            tmp_glist_ids     : [],
            selected_glist    : [],
            

        }
    },


    methods: {
        
        // reset form and close modal
        close: function () {
            this.$refs.form.reset();
            this.$parent.add_guest = 0;
            this.$parent.guest  = [];
            
            
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
            let post_url    = route('add_guest');
            let post_data  = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {

                if(res.data.status)
                {
                    this.showNotification('success', trans('em.guest')+' '+trans('em.saved')+' '+trans('em.successfully'));
                    // reload page   
                    setTimeout(function() {
                        location.reload(true);
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

        // get myglist for organiser 
        getGlist() {
            
            axios.post(route('get_myglist'),{
               
            })
            .then(res => {
                this.glist = res.data.myglist;
            
                // set mutiple myglist for multiselect list
                if(this.glist.length > 0)
                {
                    this.glist.forEach(function(v, key) {
                        this.glist_options.push({value : v.id, text : v.name });
                    }.bind(this));
                      
                }    
            })
            .catch(error => {
                Vue.helpers.axiosErrors(error);
            });
        },

        // get selected glist in case of editing
        getSelectedGlist() {
            
            // if have no guest then return
            if(Object.keys(this.guest).length <= 0)
                return true;

            // if guest have glist then return
            if(this.guest.glist <= 0)
                return true;

            // fill data 
            this.guest_id = this.guest.id;
            this.name     = this.guest.name;
            this.email    = this.guest.email;

            this.selected_glist = this.guest.glists
            
            this.tmp_glist_ids = []; 
            this.selected_glist.forEach(function (v, key) {
                this.tmp_glist_ids.push({value : v.id, text : v.name});
            }.bind(this));
            
            
        },


        // update glist for submit
        updateGlist(){
            
            this.glist_ids = [];
            
            if(this.tmp_glist_ids.length > 0)
            {
                this.tmp_glist_ids.forEach(function (value, key) {
                    this.glist_ids[key] = value.value;

                }.bind(this));
            }
            this.glist_ids = JSON.stringify(this.glist_ids);
        },


    },
    
    watch: {
        
        tmp_glist_ids : function() {
            this.updateGlist();
        },
    },

    mounted(){
             
        this.getGlist();
         
        
    }

}
</script>