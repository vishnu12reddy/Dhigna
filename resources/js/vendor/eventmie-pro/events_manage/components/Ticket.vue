<template>
    <div>
        
        <div class="modal modal-mask" v-if="openModal_1 || openModal_2">
            <div class="modal-dialog modal-container">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                        <h3 class="title">{{ (edit_ticket ? trans('em.update') : trans('em.create')) }} {{ trans('em.ticket') }}</h3>
                    </div>
                    
                    <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                        <input v-if="edit_ticket" type="hidden" class="form-control lgxname"  name="ticket_id" v-model="edit_ticket.id">
                        <input type="hidden" class="form-control lgxname"  name="event_id" v-model="event_id">
                        <input type="hidden" class="form-control lgxname"  name="organiser_id" v-model="organiser_id">
                        <input type="hidden" class="form-control lgxname"  name="taxes_ids"  v-model="taxes_ids">

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="title">{{ trans('em.title') }}</label>
                                <input type="text" class="form-control lgxname"  name="title"  v-model="title" v-validate="'required'">
                                <span v-show="errors.has('title')" class="help text-danger">{{ errors.first('title') }}</span>
                            </div>
                            <div class="form-group">
                                <label for="price">{{ trans('em.price') }} ({{ currency }})</label>
                                <input type="text" class="form-control lgxname"  name="price" v-model="price" v-validate="'required'">
                                <span v-show="errors.has('price')" class="help text-danger">{{ errors.first('price') }}</span>
                            </div>

                             <div class="form-group">
                                <label for="quantity">{{ trans('em.max_ticket_qty') }}</label>
                                <input type="text" class="form-control lgxname"  name="quantity" v-model="quantity" v-validate="'required'">
                                <span v-show="errors.has('quantity')" class="help text-danger">{{ errors.first('quantity') }}</span>
                            </div>

                            <div class="form-group">
                                <label for="customer_limit">{{ trans('em.customer_limit') }}</label>
                                <input type="text" class="form-control lgxname"  name="customer_limit" v-model="customer_limit" >
                                <span class="help text-mute">{{ trans('em.customer_limit_info') }}</span>
                                <span v-show="errors.has('customer_limit')" class="help text-danger">{{ errors.first('customer_limit') }}</span>
                            </div>

                            <div class="form-group">
                                <label for="description">{{ trans('em.description') }}</label>
                                <textarea name="description" class="form-control lgxname" rows="2" v-model="description"></textarea>
                                <span v-show="errors.has('description')" class="help text-danger">{{ errors.first('description') }}</span>
                            </div>

                            <div class="form-group">
                                <label>{{ trans('em.taxes') }}</label>
                                <multiselect
                                    v-model="tmp_taxes_ids" 
                                    :options="taxes_options" 
                                    :placeholder="'-- '+trans('em.select')+' --'" 
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
                                <label>{{ trans('em.promocodes') }}</label>
                                <multiselect
                                    v-model="tmp_promocodes_ids" 
                                    :options="promocodes_options" 
                                    :placeholder="trans('em.enter_promocode')" 
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
                            
                            <!-- CUSTOM -->
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="sale_start_date">{{ trans('em.sale_start_date') }}</label>
                                        <date-picker 
                                            v-model="sale_start_date" 
                                            type="datetime" 
                                            format="YYYY-MM-DD HH:mm:ss"
                                            :placeholder="trans('em.sale_start_date')"
                                            :class="'form-control'"
                                            :lang="$vue2_datepicker_lang"
                                        ></date-picker>
                                    
                                    </div>
                                </div>
                                
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <label for="sale_end_date">{{ trans('em.sale_end_date') }}</label>
                                        <date-picker 
                                            v-model="sale_end_date" 
                                            type="datetime" 
                                            format="YYYY-MM-DD HH:mm:ss"
                                            :placeholder="trans('em.sale_end_date')"
                                            :class="'form-control'"
                                            :lang="$vue2_datepicker_lang"
                                        ></date-picker>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="sale_price">{{ trans('em.sale_price') }} ({{ currency }})</label>
                                <input class="form-control lgxname" type="sale_price" id="sale_price" name="sale_price" v-model="sale_price">
                            </div>

                            <div class="form-group" >
                                <input type="checkbox" class="custom-control-input" :value=1 name="t_soldout" v-model="t_soldout" >
                                <label class="custom-control-label" >{{ trans('em.t_soldout') }}</label>
                            </div>

                            <div class="form-group" >
                                <input type="checkbox" class="custom-control-input" :value=1 name="is_donation" v-model="is_donation" >
                                <label class="custom-control-label" >{{ trans('em.is_donation') }}</label>
                            </div>
                            
                            <!-- CUSTOM -->


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
import mixinsFilters from '../../../../../../eventmie-pro/resources/js/mixins.js';

export default {
    props: ["edit_ticket", 'taxes', 'currency', 'openModal_1', 'openModal_2'],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            imageSrc: 'https://via.placeholder.com/200x200.jpg',
            
            title       : null,
            price       : null,
            quantity    : null,
            description : null,
            tax_id      : 0,
    
            // for taxes
            taxes_ids         : [],
            taxes_options     : [],
            tmp_taxes_ids     : [],
            selected_taxes    : [],
            customer_limit    : null,
            /* CUSTOM */
            // promocode
            promocodes            : [],
            promocodes_ids        : [],
            promocodes_options    : [],
            tmp_promocodes_ids    : [],
            ticket_promocodes     : [],
            t_soldout         : 0,
            sale_start_date   : null,
            sale_end_date     : null,
            sale_price        : null,
               
            is_donation       : 0,
            /* CUSTOM */
            
        }
    },

    computed: {
        // get global variables
        ...mapState( ['tickets', 'event_id', 'organiser_id']),
    },
    methods: {
        // update global variables
        ...mapMutations(['add', 'update']),

        // reset form and close modal
        close: function () {    
            this.$parent.edit_index  = null;
            this.$parent.openModal_1   = false;
            this.$parent.openModal_2   = false;
        },

        editTicket() {
            this.title        = this.edit_ticket.title;
            this.price        = this.edit_ticket.price;
            this.quantity     = this.edit_ticket.quantity;
            this.description  = this.edit_ticket.description;
            this.tax_id       = this.edit_ticket.tax_id ? this.edit_ticket.tax_id : 0;
            this.customer_limit       = this.edit_ticket.customer_limit;
            /* CUSTOM */
            this.getSelectedPromocodes();
            this.t_soldout    = this.edit_ticket.t_soldout > 0 ? 1 : 0;
            this.sale_start_date = (this.edit_ticket.sale_start_date != null) ? moment(this.edit_ticket.sale_start_date,'YYYY-MM-DD HH:mm:ss').tz(Intl.DateTimeFormat().resolvedOptions().timeZone).toDate() : null;

            this.sale_end_date   = this.edit_ticket.sale_end_date ? moment(this.edit_ticket.sale_end_date,'YYYY-MM-DD HH:mm:ss').tz(Intl.DateTimeFormat().resolvedOptions().timeZone).toDate() : null;
            
            this.sale_price      = this.edit_ticket.sale_price;

            this.is_donation  = this.edit_ticket.is_donation > 0 ? 1 : 0;
            
            /* CUSTOM */
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
            let post_url = route('eventmie.tickets_store');
            let post_data = new FormData(this.$refs.form);
            
            /* CUSTOM */
            post_data.append('promocodes_ids', this.promocodes_ids)
            
            if(this.sale_start_date != null){
                post_data.append('sale_start_date', moment(this.sale_start_date,'dddd LL').tz(timezone_default).format('YYYY-MM-DD HH:mm:ss'));
            }
            
            if(this.sale_end_date != null){
                post_data.append('sale_end_date', moment(this.sale_end_date,'dddd LL').tz(timezone_default).format('YYYY-MM-DD HH:mm:ss'));
            }
            
            /* CUSTOM */

            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                // on success
                // use vuex to update global sponsors array
                if(res.data.status)
                {
                    this.showNotification('success', trans('em.ticket')+' '+trans('em.saved')+' '+trans('em.successfully'));
                    this.close();
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

        updateItem() {
            this.$emit('changeItem');
        },

        // set taxes options

        setTaxesOptions(){
            // set mutiple taxes for multiselect list
            let tax_type = '';
            let tax_net_price = '';
            if(Object.keys(this.taxes).length > 0)
            {
                this.taxes.forEach(function(v, key) {
                    tax_type = v.rate_type == 'percent' ? '%' : ' '+this.currency;
                    tax_net_price = v.net_price == 'excluding' ? trans('em.exclusive') : trans('em.inclusive');

                    this.taxes_options.push({value : v.id, text : v.title+' ('+v.rate+tax_type+' '+tax_net_price+')' });
                }.bind(this));
            } 
        },
        // show selected taxes
        setSelcetedTaxes(){
            
            let tax_type = '';
            let tax_net_price = '';

            if(Object.keys(this.edit_ticket.taxes).length > 0)
            {
                // set mutiple tags for multiselect list
                
                this.tmp_taxes_ids = []; 
                this.edit_ticket.taxes.forEach(function (v, key) {
                    tax_type = v.rate_type == 'percent' ? '%' : ' '+this.currency;
                    tax_net_price = v.net_price == 'excluding' ? trans('em.exclusive') : trans('em.inclusive');

                    this.tmp_taxes_ids.push({value : v.id, text : v.title+' ('+v.rate+tax_type+' '+tax_net_price+')' });
                }.bind(this));
            
            }  
        },

        // update taxes for submit
        updateTaxes(){
            
            this.taxes_ids = [];
            
            //tags
            if(Object.keys(this.tmp_taxes_ids).length > 0)
            {
                this.tmp_taxes_ids.forEach(function (value, key) {
                    this.taxes_ids[key] = value.value;

                }.bind(this));
                
                // convert into array 
                this.taxes_ids = JSON.stringify(this.taxes_ids);
                
            }
            
        },

        /* CUSTOM */
        getPromocodes(){
            axios.get(route('get_promocodes'),{
                
            })
            .then(res => {
                if(res.data.status > 0)
                {
                    this.promocodes  = res.data.promocodes;
                    
                    // set mutiple speaker for multiselect list
                    if(this.promocodes.length > 0)
                    {
                        this.promocodes.forEach(function(v, key) {
                            this.promocodes_options.push({value : v.id, text : v.code+' ('+v.reward+(v.p_type == 'fixed' ? ' '+v.currency : '%')+' OFF)' });
                        }.bind(this));
                        
                    }   
                }
            
            })
            .catch(error => {
                Vue.helpers.axiosErrors(error);
            });    
        },

        
        // update promocodes for submit

        updatePromocodes(){
            
            this.promocodes_ids = [];
            
            //speakers
            if(this.tmp_promocodes_ids.length > 0)
            {
                var count = this.tmp_promocodes_ids.length;
                this.tmp_promocodes_ids.forEach(function (value, key) {
   
                    this.promocodes_ids[key] = value.value;
                    
                }.bind(this));
            }
        },

        //get selected promocodes for tickets
        getSelectedPromocodes(){
            axios.get(route('get_ticket_promocodes',[this.edit_ticket.id]))
            .then(res => {
                if(res.data.status > 0)
                {
                    this.ticket_promocodes = res.data.ticket_promocodes;
              
                // set mutiple sponsors for multiselect list
                if(this.ticket_promocodes.length > 0)
                {
                    this.tmp_promocodes_ids = []; 
                    this.ticket_promocodes.forEach(function (v, key) {
                        this.tmp_promocodes_ids.push({value : v.id, text : v.code+' ('+v.reward+(v.p_type == 'fixed' ? ' '+v.currency : '%')+' OFF)' });
                    }.bind(this));
                }  
                }
            
            })
            .catch(error => {
                Vue.helpers.axiosErrors(error);
            });    
        }
        /* CUSTOM */   

     
    },
    mounted() {
           /* CUSTOM */
        this.getPromocodes();
        /* CUSTOM */

        if(this.edit_ticket) {
            this.editTicket();
            
            // set selected tickets options
            this.setSelcetedTaxes();
        }
        
        // set taxes options
        this.setTaxesOptions();
        
    },

    watch: {
        tmp_taxes_ids : function() {
            this.updateTaxes();
        },

        
        /* CUSTOM */
        tmp_promocodes_ids : function() {
            
            this.updatePromocodes();
        },
        /* CUSTOM */

    },
}
</script>


<style>
    
    .mx-datepicker-popup {
        position: absolute;
        margin-top: 1px;
        margin-bottom: 1px;
        -webkit-box-shadow: 0 6px 12px rgb(0 0 0 / 18%);
        box-shadow: 0 6px 12px rgb(0 0 0 / 18%);
        z-index: 30000 !important;
    }
</style>