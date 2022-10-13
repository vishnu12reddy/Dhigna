<template>
    <div>
        <div class="modal modal-mask" v-if="event_id > 0">
            <div class="modal-dialog modal-container">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                        <h3 class="title">{{  trans('em.event') +' '+ trans('em.sub_organizers') }} </h3>
                    </div>
                    
                    <form ref="form" :action="submitRoute" method="POST" enctype="multipart/form-data">
                        
                        <input type="hidden" class="form-control lgxname"  name="event_id" v-model="event_id">
                        <input type="hidden" class="form-control lgxname"  name="pos_ids"  v-model="pos_ids">
                        <input type="hidden" class="form-control lgxname"  name="scanner_ids"  v-model="scanner_ids">
                        <input type="hidden" name="_token" :value="csrf">

                        <input type="hidden" class="form-control lgxname"  name="organiser_id" v-model="organiser_id">

                        <div class="modal-body">

                            <div class="form-group">
                                <label>{{ trans('em.pos')  }}</label>
                                <multiselect
                                    v-model="tmp_pos_ids" 
                                    :options="pos_options" 
                                    :placeholder="'-- '+trans('em.select')+' '+trans('em.pos')+' --'" 
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

                            <div class="form-group">
                                <label>{{ trans('em.scanner') }}</label>
                                <multiselect
                                    v-model="tmp_scanner_ids" 
                                    :options="scanner_options" 
                                    :placeholder="'-- '+trans('em.select')+' '+trans('em.scanner')+' --'" 
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
                            <button type="submit" class="btn lgx-btn btn-block"><i class="fas fa-sd-card"></i> {{ trans('em.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</template>

<script>

import { mapState, mapMutations} from 'vuex';
import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';
import Multiselect from 'vue-multiselect';

export default {
    props: ["event_id", "sub_organizers", "organiser_id", "is_admin"],

       components: {
        Multiselect,
    },

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            submitRoute : route('save_sub_organizers'),

            // for pos sub organizers
            organizer_users : [],

            pos_ids         : [],
            pos_options     : [],
            tmp_pos_ids     : [],
            selected_pos    : [],

            
            // for scanner sub organizers
            scanner_ids         : [],
            scanner_options     : [],
            tmp_scanner_ids     : [],
            selected_scanner    : [],
            
        }
    },

    computed: {
     
        csrf() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        },
    },
    methods: {
        // update global variables
        ...mapMutations(['add', 'update']),

        // reset form and close modal
        close: function () {    
            this.$parent.s_event_id    = 0;
        },

        // set pos sub organizers options

        setPosOptions(){
           
            // set mutiple pos sub organiser for multiselect list
            if(Object.keys(this.organizer_users).length > 0)
            {
                if(typeof this.organizer_users['4'] !== 'undefined')
                {
                    this.organizer_users['4'].forEach(function(v, key) {
                        this.pos_options.push({value : v.id, text : v.name+' '+'('+v.email+')' });
                    }.bind(this));
                }    
                        
            } 
        },

        // show selected pos sub organizers
        setSelcetedPos(){
            
            if(Object.keys(this.sub_organizers).length > 0)
            {
                // set mutiple tags for multiselect list
                if(typeof this.sub_organizers['4'] !== 'undefined')
                {
                    this.tmp_pos_ids = []; 
                    this.sub_organizers['4'].forEach(function (v, key) {
                        this.tmp_pos_ids.push({value : v.user_id, text : v.name+' '+'('+v.email+')' });
                    }.bind(this));
                }
            }  
        },

        // update pos for submit
        updatePos(){
            
            this.pos_ids = [];
            
            //tags
            if(Object.keys(this.tmp_pos_ids).length > 0)
            {
                this.tmp_pos_ids.forEach(function (value, key) {
                    this.pos_ids[key] = value.value;

                }.bind(this));
                
                // convert into array 
                this.pos_ids = JSON.stringify(this.pos_ids);
                
            }
            
        },


        
        // set scanner sub organizers options

        setScannerOptions(){
            // set mutiple scanner sub organiser for multiselect list
            if(Object.keys(this.organizer_users).length > 0)
            {
                if(typeof this.organizer_users['5'] !== 'undefined')
                {
                    this.organizer_users['5'].forEach(function(v, key) {
                        this.scanner_options.push({value : v.id, text : v.name+' '+'('+v.email+')' });
                    }.bind(this));
                }   
            } 
        },


        // show selected Scanner sub organizers
        setSelcetedScanner(){

            if(Object.keys(this.sub_organizers).length > 0)
            {
                // set mutiple Scanner for multiselect list
                if(typeof this.sub_organizers['5'] !== 'undefined')
                {
                    this.tmp_scanner_ids = []; 
                    this.sub_organizers['5'].forEach(function (v, key) {
                        this.tmp_scanner_ids.push({value : v.user_id, text : v.name+' '+'('+v.email+')' });
                    }.bind(this));
                }
            }  
        },


        // update Scanner for submit
        updateScanner(){
            
            this.scanner_ids = [];
            
            //tags
            if(Object.keys(this.tmp_scanner_ids).length > 0)
            {
                this.tmp_scanner_ids.forEach(function (value, key) {
                    this.scanner_ids[key] = value.value;

                }.bind(this));
                
                // convert into array 
                this.scanner_ids = JSON.stringify(this.scanner_ids);
                
            }
            
        },

        // get sub-organizers

        getSubOrganizers(){

            let _this   = this;

            axios.post(route('get_organizer_users'),{
                    'organiser_id' : this.organiser_id
                })
                
            .then(res => {
                
                var promise = new Promise(function(resolve, reject) { 
                    
                    _this.organizer_users  = res.data.sub_organizers;
                    
                    resolve(true);
                }); 

                promise.then(function(successMessage)  { 
                        
                    // for pos sub organizers
                    _this.setPosOptions();
                    _this.setSelcetedPos();

                    
                    // for scanner sub organizers
                    _this.setScannerOptions();
                    _this.setSelcetedScanner();
                    
                }, function(errorMessage) { 
                //error handler function is invoked 
                    console.log(errorMessage); 
                })
                

            })
            .catch(error => {
                
            });
        
        }

    },

    watch: {
        
        tmp_pos_ids : function() {
            this.updatePos();
        },

        tmp_scanner_ids : function() {
            this.updateScanner();
        },
    },

    mounted() {
        
        this.getSubOrganizers();
        
        
          
    },
}
</script>