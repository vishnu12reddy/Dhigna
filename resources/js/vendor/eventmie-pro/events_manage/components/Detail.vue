<template>
    <div class="tab-pane active">
        <div class="panel-group">
            <div class="panel panel-default lgx-panel">
                <div class="panel-heading">
                    <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data" class="lgx-contactform">
                        <input type="hidden" name="event_id" v-model="event_id">
                       
                        <input type="hidden" name="organiser_id" v-model="organiser_ids" v-validate="(is_admin ? 'required' : '')" >

                        <!-- it is display in create case and when organiser_id is null -->
                        <div class="form-group" v-if="organisers.length > 0">
                            <label>  {{ trans('em.organiser') }}</label>
                            <div v-if="!organiser_id">
                                <v-select 
                                    label="name" 
                                    class="style-chooser" 
                                    :placeholder="trans('em.search_organiser')+' '+trans('em.email')+'/'+trans('em.name')"
                                    v-model="organizer" 
                                    :required="!organizer" 
                                    :filterable="false" 
                                    :options="options" 
                                    @search="onSearch" 
                                    @change="isDirty()"
                                ><div slot="no-options">{{ trans('em.organiser_not_found') }} </div></v-select>
                            </div>

                              <!-- it is display in edit case and when organiser_id is   -->
                            <input v-if="organiser_id" readonly type="text"  class="form-control" :value="organizer.name+'  ( '+organizer.email+' )'">
                                
                            <span v-show="errors.has('organiser_id')" class="help text-danger">{{ errors.first('organiser_id') }}</span>
                            
                        </div>
                       
                       <!-- Only show this to admin -->
                        <div v-if="organisers.length <= 0 && Object.keys(event).length <= 0 && is_admin">
                            <div class="alert alert-danger">{{ trans('em.add_organiser') }} </div>
                        </div>
    
                        <div class="form-group">
                            <label>{{ trans('em.select_category') }}</label>
                            <select name="category_id" class="form-control" v-model="category_id" v-validate="'required|decimal|is_not:0'" @change="isDirty()">
                                <option value="0">-- {{ trans('em.category') }} --</option>
                                
                                <option v-for="(category, index) in categories" :key = "index" :value="category.id">{{category.name}}</option>

                            </select>
                            <span v-show="errors.has('category_id')" class="help text-danger">{{ errors.first('category_id') }}</span>    
                        </div>
                        
                        <div class="form-group">
                            <label>{{ trans('em.event_name') }}</label>
                            <input type="text" class="form-control"  name="title" v-model="title" v-validate="'required'" @change="isDirty()">
                            <span v-show="errors.has('title')" class="help text-danger">{{ errors.first('title') }}</span>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('em.event_url') }}</label>
                            <input type="hidden" name="slug" v-model="slug" v-validate="'required'" @change="isDirty()">
                            <p class="help">{{ slugUrl() }}</p>
                        </div>

                        <!-- CUSTOM -->
                        <div class="form-group">
                            <label>{{ trans('em.short_url') }}</label>
                            <div class="input-group">
                                <span class="input-group-addon" id="basic-addon3">{{ short_url }}</span>
                                <input type="text" class="form-control" name="short_url" v-model="short" @change="isDirty()">
                                <span v-show="errors.has('short_url')" class="help text-danger">{{ errors.first('short_url') }}</span>
                            </div>
                        </div>
                        <!-- CUSTOM -->

                        <div class="form-group">
                            <label>{{ trans('em.excerpt') }} ({{ trans('em.short_info') }})</label>
                            <input type="text" class="form-control"  name="excerpt" v-model="excerpt" v-validate="'required'" @change="isDirty()">
                            <span v-show="errors.has('excerpt')" class="help text-danger">{{ errors.first('excerpt') }}</span>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('em.description') }}</label>
                            <textarea class="form-control"  rows="3" name="description" :value="description" v-validate="'required'" style="display:none;"></textarea>
                            <ckeditor  v-model="description"></ckeditor>
                            <span v-show="errors.has('description')" class="help text-danger">{{ errors.first('description') }}</span>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('em.more_event_info') }} </label>
                            <textarea class="form-control" rows="3" name="faq" :value="faq" style="display:none;"></textarea>
                            <ckeditor v-model="faq"></ckeditor>
                            <span v-show="errors.has('faq')" class="help text-danger">{{ errors.first('faq') }}</span>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('em.offline_payment_info') }} </label>
                            <textarea class="form-control"  rows="3" name="offline_payment_info" v-model="offline_payment_info" ></textarea>
                            <span class="help text-mute">{{ trans('em.offline_payment_info_ie') }}</span>
                        </div>

                        <div class="form-group">
                            <label>Bank Payment Info </label>
                            <textarea class="form-control"  rows="3" name="bank_payment_info" v-model="bank_payment_info" ></textarea>
                            <span class="help text-mute">Instructions to show under Bank Payment Option</span>
                        </div>

                        <div class="form-group">
                            <label>Card Payment Info </label>
                            <textarea class="form-control"  rows="3" name="card_payment_info" v-model="card_payment_info" ></textarea>
                            <span class="help text-mute">Instructions to show under Card Payment Option</span>
                        </div>

                        
                        <!-- CUSTOM -->
                        <div class="form-group" >
                            <label >{{ trans('em.event_specific_currency') }}</label>
                            <input type="text" class="form-control" name="currency" v-model="currency" @change="isDirty()" placeholder="e.g USD">
                            <span v-show="errors.has('currency')" class="help text-danger">{{ errors.first('currency') }}</span>    
                        </div>

                        <div class="form-group" v-if="is_admin">
                            <label>{{ trans('em.e_admin_commission') }}</label>
                            <input type="number" class="form-control"  name="e_admin_commission" v-model="e_admin_commission" @change="isDirty()">
                        </div>

                        <div class="form-group" v-if="is_admin">
                            <input type="checkbox" class="custom-control-input" :value=1 name="featured" v-model="featured" @change="isDirty()">
                            <label class="custom-control-label" >{{ trans('em.event_featured') }}</label>
                        </div>

                        <div class="form-group" v-if="is_admin">
                            <input type="checkbox" class="custom-control-input" :value=1 name="status" v-model="status" @change="isDirty()">
                            <label class="custom-control-label" >{{ trans('em.event_status') }}</label>
                        </div>

                        <div class="form-group" >
                            <input type="checkbox" class="custom-control-input" :value=1 name="e_soldout" v-model="e_soldout" @change="isDirty()">
                            <label class="custom-control-label" >{{ trans('em.e_soldout') }}</label>
                        </div>
                        
                        <div class="form-group" >
                            <input type="checkbox" class="custom-control-input" :value=1 name="show_reviews" v-model="show_reviews" @change="isDirty()">
                            <label class="custom-control-label" >{{ trans('em.show_reviews') }}</label>
                        </div>
                        <!-- CUSTOM -->
                        
                        <button type="submit" class="btn lgx-btn btn-block"><i class="fas fa-sd-card"></i> {{ trans('em.save') }}</button>
                    </form>                
                    
                </div>
            </div>
        </div>

        
        
    </div>
</template>

<script>

import _ from 'lodash';
import { mapState, mapMutations} from 'vuex';
import mixinsFilters from '../../../../../../eventmie-pro/resources/js/mixins.js';


export default {
    props: [
        'organisers', 'is_admin', 'event_ck', 'selected_organiser'
    ],
    
    mixins:[
        mixinsFilters
    ],

    data() {
        return {

            title           : null,
            excerpt         : null,
            organiser_ids   : null,
            categories      : [],
            description     : this.event_ck.description,
            faq             : this.event_ck.faq,
            category_id     : 0,
            featured        : 0,
            status          : 0,

            // organizers options
            options         : this.organisers,
            //selected organizer
            organizer       : this.selected_organiser,
            offline_payment_info :  null,
            bank_payment_info :  null,
            card_payment_info : null,

            //CUSTOM
            e_admin_commission :   null,
            
            short           : '',
            short_url       : route('eventmie.welcome')+'/',
            currency        : '',
            e_soldout       : 0,

            show_reviews    : 0,
            //CUSTOM
        }
    },

    computed: {
        // get global variables
        ...mapState( ['event_id', 'organiser_id', 'event', 'is_dirty']),
        
        slug: function() {
            if(this.title != null)
            {
                var slug = this.sanitizeTitle(this.title);
                return slug;
            }
        }
    },

    methods: {

        // update global variables
        ...mapMutations(['add', 'update']),

        editEvent( editor ) {
            
            if(Object.keys(this.event).length > 0)
            {
                this.title          = this.event.title;
                this.excerpt        = this.event.excerpt;
                this.category_id    = this.event.category_id;
                this.organiser_ids  = this.organiser_id ;
                this.featured       = this.event.featured > 0 ? 1 : 0; 
                this.status         = this.event.status > 0 ? 1 : 0;
                this.offline_payment_info = this.event.offline_payment_info;
                this.bank_payment_info = this.event.bank_payment_info;
                this.card_payment_info = this.event.card_payment_info;
                //CUSTOM
                this.e_admin_commission = this.event.e_admin_commission;
                this.short           = (this.event.short_url == '' || this.event.short_url == null ) ? '' : this.event.short_url;
                this.currency        = this.event.currency;
                this.e_soldout       = this.event.e_soldout > 0 ? 1 : 0;
                this.show_reviews    = this.event.show_reviews > 0 ? 1 : 0;
                //CUSTOM
            }    
            
            
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
            let post_url = route('eventmie.myevents_store');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                // on success
                // use vuex to update global sponsors array
                if(res.data.status)
                {
                    // fill data to global sponsors array
                    this.add({  
                        event_id        : res.data.id,
                        organiser_id    : res.data.organiser_id , 
                    });
                    this.showNotification('success', trans('em.event_save_success'));
                    
                    if(res.data.slug)
                    {   
                        //create case redirect with slug
                        setTimeout(function() {
                            window.location = route('eventmie.myevents_form',[res.data.slug]);
                        }, 1000);
                    }
                }    

            })
            .catch(error => {
                let serrors = Vue.helpers.axiosErrors(error);
                if (serrors.length) {
                    this.serverValidate(serrors);
                }
            });
        },

        getCategories(){
            let post_url = route('eventmie.myevents_categories');
            
            // axios post request
            axios.get(post_url)
            .then(res => {
                
                if(res.data.status)
                {
                    this.categories = res.data.categories;
                }
                
            })
            .catch(error => {
                let serrors = Vue.helpers.axiosErrors(error);
                if (serrors.length) {
                    this.serverValidate(serrors);
                }
            });
        },

        // slug route
        slugUrl(){
            if(this.slug != null)
                return route('eventmie.events_index')+'/'+this.slug;

            return '';
        },

        // get organizers

        getOrganizers(loading, search = null){
            var postUrl     = route('eventmie.get_organizers');
            var _this       = this;
            axios.post(postUrl,{
                'search' :search,
            }).then(res => {
                
                var promise = new Promise(function(resolve, reject) { 
                    _this.options = res.data.organizers;
                    resolve(true);
                }) 
                
                promise 
                    .then(function(successMessage) { 
                        loading(false);
                    }, function(errorMessage) { 
                    //error handler function is invoked 
                        console.log(errorMessage); 
                    }) 
            })
            .catch(error => {
                let serrors = Vue.helpers.axiosErrors(error);
                if (serrors.length) {
                    this.serverValidate(serrors);
                }
            });
        },
        
        // v-select methods
        onSearch(search, loading) {
            loading(true);
            this.search(loading, search, this);
        },

        // v-select methods
        search: _.debounce((loading, search, vm) => {
            
            if(search.length > 0)
                vm.getOrganizers(loading, search);
            else
                loading(false);    
            
        }, 350),


        isDirty() {
            this.add({is_dirty: true});
        },
        isDirtyReset() {
            this.add({is_dirty: false});
        },

         //CUSTOM
         // slug route
         shortUrl(){
            this.short_url     = '';
            
            if(this.short.length > 0)
                this.short_url     = route('eventmie.welcome')+'/'+this.sanitizeTitle(this.short);
            else{

                this.short_url     = route('eventmie.welcome')+'/';
                this.short         = '';
            }    
        },

         // when update title then update short
        updateTitle(){
            
            if(this.title.length > 0 && this.event_id <= 0){
                this.short_url       = '';
                this.short           = this.sanitizeTitle(this.title);
                this.short_url       = route('eventmie.welcome')+'/'+this.sanitizeTitle(this.title);
            }     
        }
        //CUSTOM


    },

    mounted(){
        
        this.isDirtyReset();
        if(this.categories.length == 0)
            this.getCategories();
        
        if(this.event_id) {
            var $this = this;
            
            this.getMyEvent().then(function (response){
                $this.editEvent();  
            });
            
        };
    },

    watch: {
        // active when organizer search 
        organizer: function () {
            this.organiser_ids = this.organizer != null ?  this.organizer.id : null;
        },

        
        /* CUSTOM */
         title: function() {
            this.updateTitle();
        },

        short : function(){
            this.shortUrl();
        }
        /* CUSTOM */
    }

    
}
</script>