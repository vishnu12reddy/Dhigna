<template>
    <div class="row">
        <div class="col-md-12">
        
            <div class="modal modal-mask" v-if="g_event_id > 0">
                <div class="modal-dialog modal-container">
                    <div class="modal-content lgx-modal-box">
                        <div class="modal-header">
                            <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                            <h3 class="title"> {{ trans('em.add_attendees_to_guestlist') }}</h3>
                            
                        </div>
                        
                        <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" class="form-control lgxname" v-model="glist_ids" name="glist_ids"
                            >
                            <input type="hidden" class="form-control lgxname" v-model="organiser_id
                            " name="organiser_id">
                            
                            <input type="hidden" class="form-control lgxname" v-model="g_event_id" name="event_id"
                            >
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>{{ trans('em.select') }} {{ trans('em.guestlist') }}</label>
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
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn lgx-btn"><i class="fas fa-sd-card"></i> {{ trans('em.add')+' '+trans('em.to')+' '+trans('em.guestlist') }}</button>
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

    components: {
        Multiselect,
        
    },

    props: ['g_event_id', 'organiser_id'],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
           name : null,
               
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
            this.$parent.g_event_id = 0;
            
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
            let post_url = route('add_to_glist');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                
                this.close();
                
                if(res.data.status)
                    this.showNotification('success',  trans('em.guestlist')+' '+trans('em.saved')+' '+trans('em.successfully'));
                
                if(!res.data.status)
                    this.showNotification('error', res.data.msg);

                // reload page   
                setTimeout(function() {
                    location.reload(true);
                }, 1000);
            
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
                organiser_id: this.organiser_id

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