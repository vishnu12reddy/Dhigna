<template>
<div>
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="exampleFormControlSelect1">{{ trans('em.events') }}</label>
                    <select class="form-control" name="state" v-model="event_id" >
                        <option  value=0>{{ trans('em.all_events') }} </option>
                        <option v-for="(event, index) in events" :key ="index" :value="event.id">{{event.title}} </option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="exampleFormControlSelect1">{{ trans('em.booking_date') }} </label>
                    <date-picker  class="form-control" :shortcuts="shortcuts" v-model="date_range" range :lang="$vue2_datepicker_lang" :placeholder="trans('em.booking_date')" format="YYYY-MM-DD "></date-picker>
                </div>
            </div>   

              <!-- CUSTOM -->

             <div class="col-md-6">
                <div class="form-group">
                    <label for="exampleFormControlSelect1">{{ trans('em.event_date') }} </label>
                    <date-picker  class="form-control" :shortcuts="shortcuts1" v-model="event_date_range" range :lang="$vue2_datepicker_lang" :placeholder="trans('em.event_date')" format="YYYY-MM-DD "></date-picker>
                </div>
            </div>    

            <div class="col-md-6">
                <div class="form-group">
                    <label for="exampleFormControlSelect1">{{ trans('em.search') }} {{ trans('em.any') }}</label>
                    <input class="form-control" type="text" v-model="search" :placeholder="trans('em.search')" @keyup="resetPagination()">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="exampleFormControlSelect1">{{ trans('em.show') }}</label>
                    <select class="form-control" v-model="length" @change="resetPagination()">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                        <option value="500">500</option>
                    </select>
                </div>
            </div>

             <!-- CUSTOM --> 
        </div>
    </div>
    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-md-12 table-responsive table-mobile">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                               <!-- CUSTOM  -->
                            <th v-for="column in columns" :key="column.name" @click="sortBy(column.name)"
                            
                                :class="[
                                    sortKey === column.name ? (sortOrders[column.name] > 0 ? 'sorting_asc' : 'sorting_desc') : 'sorting',
                                    column.hide ? 'hidden-xs' : '',
                                ]"
                                style="cursor:pointer;">
                                {{column.label}} <i class="fa fa-sort" aria-hidden="true"></i>
                            </th>
                            
                            
                            <!-- CUSTOM  -->
                            <th>{{ trans('em.expired') }}</th>
                            <th>{{ trans('em.download') }}</th>
                            <th>{{ trans('em.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(booking, index) in paginatedBookings" :key="index" >
                            <td :data-title="trans('em.order_id')"><strong>#{{ booking.order_number }}</strong></td>
                            <td :data-title="trans('em.event')">
                                <a :href="eventSlug(booking.event_slug)">{{ booking.event_title+' ('+booking.event_category+')' }}</a>
                                <br><br>
                                <p class="text-bold text-small">{{ trans('em.timings') }}</p>
                                
                                <p class="text-small" v-if="booking.event_start_date != booking.event_end_date">{{ moment(userTimezone(booking.event_start_date+' '+booking.event_start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')).format(date_format.vue_date_format)+' - '+moment(userTimezone(booking.event_end_date+' '+booking.event_end_time,'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')).format(date_format.vue_date_format) }}</p>
                                <p class="text-small" v-else>{{ moment(userTimezone(booking.event_start_date+' '+booking.event_start_time,'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')).format(date_format.vue_date_format) }}</p>

                               <p class="text-small">{{ userTimezone(booking.event_start_date+' '+booking.event_start_time, 'YYYY-MM-DD HH:mm:ss').format(date_format.vue_time_format)+' - '+userTimezone(booking.event_end_date+' '+booking.event_end_time,'YYYY-MM-DD HH:mm:ss').format(date_format.vue_time_format) }}</p>

                               <p class="text-samll"> {{ showTimezone()  }}</p>

                            </td>
                            <td :data-title="trans('em.customer_email')">{{ booking.customer_email}}</td>
                            <td :data-title="trans('em.ticket')">{{ booking.ticket_title }} <strong>{{ ' x '+booking.quantity }}</strong></td>
                            <!-- <td :data-title="trans('em.order_total')">{{ booking.net_price+' '+currency }}</td> -->
                            <!-- CUSTOM -->
                            <td :data-title="trans('em.order_total')">{{ booking.net_price+' '+ booking.currency }} </td>
                            <td :data-title="trans('em.reward')">{{ (booking.promocode_reward != null ? booking.promocode_reward : 0 )+' '+ booking.currency }} </td>
                            <!-- CUSTOM -->
                            <td :data-title="trans('em.booked_on')">{{ moment(userTimezone(booking.created_at, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')).format(date_format.vue_date_format) }} {{ showTimezone()  }}</td>
                            <td :data-title="trans('em.payment')" class="text-capitalize">
                                <span class="lgx-badge lgx-badge-small lgx-badge-mute" v-if="booking.payment_type == 'offline'">
                                    {{ booking.payment_type }} 
                                    <hr class="small">
                                    <small class="text-small text-success" v-if="booking.is_paid">{{ trans('em.paid') }}</small>
                                    <small class="text-small text-danger" v-else>{{ trans('em.unpaid') }}</small>
                                </span>
                                <span class="lgx-badge lgx-badge-small lgx-badge-success" v-else>{{ booking.payment_type }} <hr class="small"><small class="text-small">{{ booking.is_paid ? trans('em.paid') : trans('em.unpaid') }}</small></span>
                            </td>
                            <td :data-title="trans('em.checked_in')">
                                <span class="lgx-badge lgx-badge-small lgx-badge-success" v-if="booking.checked_in > 0">
                                    {{ trans('em.yes') }}
                                    <hr class="small">
                                    <small class="text-small text-white">{{ booking.checked_in +'/'+ booking.quantity }}</small>
                                </span>
                                <span class="lgx-badge lgx-badge-small lgx-badge-mute" v-else>{{ trans('em.no') }}</span>
                            </td>
                            <td :data-title="trans('em.status')">
                                <span class="lgx-badge lgx-badge-small lgx-badge-success" v-if="booking.status == 1">{{ trans('em.enabled') }}</span>
                                <span class="lgx-badge lgx-badge-small lgx-badge-mute" v-else>{{ trans('em.disabled') }}</span>
                            </td>
                            <td :data-title="trans('em.cancellation')">
                                <span class="lgx-badge lgx-badge-small lgx-badge-success" v-if="booking.booking_cancel == 0 && booking.status == 1">{{ trans('em.no') }}</span>
                                <span class="lgx-badge lgx-badge-small lgx-badge-mute" v-if="booking.booking_cancel == 0 && booking.status == 0">{{ trans('em.disabled') }}</span>
                                <span class="lgx-badge lgx-badge-small lgx-badge-warning" v-if="booking.booking_cancel == 1">{{ trans('em.pending') }}</span>
                                <span class="lgx-badge lgx-badge-small lgx-badge-info" v-if="booking.booking_cancel == 2">{{ trans('em.approved') }}</span>
                                <span class="lgx-badge lgx-badge-small lgx-badge-mute" v-if="booking.booking_cancel == 3">{{ trans('em.refunded') }}</span>
                            </td>

                            <!-- check booking expired or not -->
                            <td :data-title="trans('em.expired')" v-if="(moment(booking.event_end_date+' '+booking.event_end_time, 'YYYY-MM-DD HH:mm:ss').tz(Intl.DateTimeFormat().resolvedOptions().timeZone) <= moment().tz(Intl.DateTimeFormat().resolvedOptions().timeZone))">
                                <span class="lgx-badge lgx-badge-small lgx-badge-danger"> {{trans('em.yes')}} </span>
                            </td>

                            <td :data-title="trans('em.expired')" v-else>
                                <span class="lgx-badge lgx-badge-small lgx-badge-primary"> {{trans('em.no')}} </span>
                            </td>
                            <!-- check booking expired or not -->
                            <td :data-title="trans('em.download')">
                                <div v-if="hide_ticket_download == null">
                                    <a v-if="booking.is_paid == 1 && booking.status == 1" class="lgx-btn lgx-btn-sm lgx-btn-success" :href="downloadURL(booking.id, booking.order_number)"><i class="fas fa-download"></i> {{trans('em.ticket')}}</a>
                                    <!-- CUSTOM -->
                                    <a v-if="booking.is_paid == 1 && booking.status == 1" class="lgx-btn lgx-btn-sm lgx-btn-success" :href="downloadInvoice(booking.id)"><i class="fas fa-download"></i> {{trans('em.invoice')}}</a>
                                    <!-- CUSTOM -->

                                    <span class="lgx-badge lgx-badge-small lgx-badge-mute" v-else>
                                        <small v-if="booking.is_paid == 0 && booking.status == 1" class="text-small text-danger">{{ trans('em.unpaid') }}</small>
                                        <small v-else class="text-small">{{ trans('em.disabled') }}</small>
                                    </span>
                                </div>

                                <div v-if="booking.online_location != null && booking.is_paid == 1 && booking.status == 1"> 
                                    <button type="button" class="lgx-btn lgx-btn-sm mb-3" @click="booking_id = booking.id"><i class="fas fa-tv"></i> {{ trans('em.online_event')}}</button>
                                    <online-event  v-if="booking_id == booking.id" :online_location="booking.online_location" :booking_id="booking.id" ></online-event>
                                </div>

                                   <!-- CUSTOM -->
                                <div v-if="booking.youtube_embed == null && booking.vimeo_embed == null">
                                    <div v-if="booking.online_location != null"> 
                                        <button type="button" class="lgx-btn lgx-btn-sm mb-3" @click="booking_id = booking.id"><i class="fas fa-tv"></i> {{ trans('em.online_event')}}</button>
                                        <online-event  v-if="booking_id == booking.id" :online_location="booking.online_location" :booking_id="booking.id" ></online-event>
                                    </div>
                                </div>
                                <div v-else>
                                    <div v-if="booking.youtube_embed != null && booking.is_paid == 1 && booking.status == 1"> 
                                        <button type="button" class="lgx-btn lgx-btn-sm mb-3" @click="embed_code_modal=1;embed_code=booking.youtube_embed;online_location=booking.online_location"><i class="fab fa-youtube"></i> YouTube</button>
                                    </div>
                                    <div v-if="booking.vimeo_embed != null && booking.is_paid == 1 && booking.status == 1"> 
                                        <button type="button" class="lgx-btn lgx-btn-sm"  @click="embed_code_modal=1;embed_code=booking.vimeo_embed;online_location=booking.online_location"><i class="fab fa-vimeo"></i> Vimeo</button>
                                    </div>
                                </div>
                                <!-- CUSTOM -->
                            </td>
                            <td :data-title="trans('em.actions')">
                                <!-- CUSTOM -->
                                <div v-if="booking.is_paid == 1 && booking.status == 1" class="mb-2"> 
                                    <button type="button" class="lgx-btn lgx-btn-sm btn-block" @click="is_qrcode = 1;qrcode_booking_id = booking.id"><i class="fas fa-qrcode"></i> {{ trans('em.check_in')}}</button>
                                    <qr-code v-if="is_qrcode > 0 && qrcode_booking_id == booking.id" :is_qrcode="is_qrcode"  :qrcode_booking_id="qrcode_booking_id" :order_number="booking.order_number" :booking="booking"></qr-code>
                                </div>
                                <!-- CUSTOM -->

                                <a class="lgx-btn lgx-btn-sm lgx-btn-black" :href="goto_route(booking.id)">
                                    <i class="fas fa-eye"></i> <span>{{ trans('em.view') }}</span>
                                </a><br>
                                <button type="button" class="lgx-btn lgx-btn-sm lgx-btn-info" @click="edit_index = index">
                                    <i class="fas fa-edit"> </i><span >{{ trans('em.edit') }}</span>
                                </button>
                                <edit-booking 
                                    :booking    = "booking"
                                    v-if="edit_index == index" 
                                    @changeItem = "getOrganiserBookings"

                                ></edit-booking>
                            </td>
                        </tr>
                
                    </tbody>
                </table>
            </div>
             <!-- CUSTOM -->
            
            <embed-code  v-if="embed_code_modal > 0" :embed_code="embed_code" :embed_code_modal="embed_code_modal" :online_location="online_location"></embed-code>
            <!-- CUSTOM -->
        </div>

        <div class="row">
            <div class="col-md-12 text-center" v-if="bookings.length > 0">
                <pagination-component v-if="pagination.last_page > 1" :pagination="pagination" :offset="pagination.total" :path="'/bookings'" @paginate="getOrganiserBookings()">
                </pagination-component>
            </div>       
        </div>
    </div>
</div>
</template>

<script>

import PaginationComponent from '../../../../../../eventmie-pro/resources/js/common_components/Pagination';
import mixinsFilters from '../../../../mixins.js';
import EditBooking from '../../../../../../eventmie-pro/resources/js/bookings_organiser/components/EditBooking.vue';
import DatePicker from 'vue2-datepicker';
import OnlineEvent from '../../../../../../eventmie-pro/resources/js/bookings_customer/components/OnlineEvent';

//CUSTOM
import EmbedCode from './custom/EmbedCode';
import _ from 'lodash';
import QrCode from './custom/QrCode';
//CUSTOM

export default {
    
    mixins:[
        mixinsFilters
    ],

    props: [
        // pagination query string
        'page',
        'is_success',
        'date_format',
        'hide_ticket_download'
        
    ],
    
    components: {
        PaginationComponent,
        EditBooking,
        DatePicker,
        OnlineEvent,
        //CUSTOM
        EmbedCode,
        QrCode,
        //CUSTOM
    },

    
    data() {
          //CUSTOM
        let sortOrders = {};
        let columns = [
            {label: trans('em.order_id'), name: 'order_number' , hide: true },
            {label: trans('em.event'), name: 'event_slug' , hide: false },
            {label: trans('em.customer')+' '+trans('em.email'), name: 'customer_email', hide: true },
            {label: trans('em.ticket'), name: 'ticket_title', hide: true },
            {label: trans('em.order')+' '+trans('em.total'), name: 'net_price', hide: true },
            {label: trans('em.reward'), name: 'promocode_reward', hide: true },
            {label: trans('em.booked')+' '+trans('em.on') , name: 'created_at', hide: true },
            {label: trans('em.payment') , name: 'is_paid', hide: true },
            {label: trans('em.checked_in') , name: 'checked_in', hide: true },
            {label: trans('em.status') , name: 'status', hide: true },
            {label: trans('em.cancellation') , name: 'booking_cancel', hide: true },
        ];
        columns.forEach((column) => {
            sortOrders[column.name] = -1;
        });
        //CUSTOM
        return {
            bookings   : [],
            moment     : moment,
            edit_index : null,
             pagination: {
                'current_page': 1
            },
            currency   : null,

            date_range : [],
            start_date : '',
            end_date   : '',

            booking_id : 0,
            
            // date shortucts like today, tommorrow
            shortcuts: [
                {
                    text: trans('em.today'),
                    onClick: () => {
                        this.date_range = [moment().toDate(), moment().toDate() ]
                    }
                },
                {
                    text: trans('em.tomorrow'),
                    onClick: () => {
                        this.date_range = [moment().add(1,'day').toDate(), moment().add(1,'day').toDate()]
                    }
                },
                {
                    text: trans('em.this')+' '+trans('em.weekend'),
                    onClick: () => {
                        this.date_range = [moment().endOf("week").toDate(), moment().endOf("week").toDate()]
                    }
                },
                {
                    text: trans('em.this')+' '+trans('em.week'),
                    onClick: () => {
                        this.date_range = [moment().startOf("week").toDate(), moment().endOf("week").toDate()]
                    }
                },
                {
                    text: trans('em.next')+' '+trans('em.week'),
                    onClick: () => {
                        this.date_range = [moment().add(1, 'weeks').startOf("week").toDate(), moment().add(1, 'weeks').endOf("week").toDate()]
                    }
                },
                {
                    text: trans('em.this')+' '+trans('em.month'),
                    onClick: () => {
                        this.date_range = [moment().startOf("month").toDate(), moment().endOf("month").toDate()]
                    }
                },
                {
                    text: trans('em.next')+' '+trans('em.month'),
                    onClick: () => {
                        this.date_range = [moment().add(1, 'months').startOf("month").toDate(), moment().add(1, 'months').endOf("month").toDate()]
                    }
                },
            ],
             //CUSTOM
            shortcuts1: [
                {
                    text: trans('em.today'),
                    onClick: () => {
                        this.event_date_range = [moment().toDate(), moment().toDate() ]
                    }
                },
                {
                    text: trans('em.tomorrow'),
                    onClick: () => {
                        this.event_date_range = [moment().add(1,'day').toDate(), moment().add(1,'day').toDate()]
                    }
                },
                {
                    text: trans('em.this')+' '+trans('em.weekend'),
                    onClick: () => {
                        this.event_date_range = [moment().endOf("week").toDate(), moment().endOf("week").toDate()]
                    }
                },
                {
                    text: trans('em.this')+' '+trans('em.week'),
                    onClick: () => {
                        this.event_date_range = [moment().startOf("week").toDate(), moment().endOf("week").toDate()]
                    }
                },
                {
                    text: trans('em.next')+' '+trans('em.week'),
                    onClick: () => {
                        this.event_date_range = [moment().add(1, 'weeks').startOf("week").toDate(), moment().add(1, 'weeks').endOf("week").toDate()]
                    }
                },
                {
                    text: trans('em.this')+' '+trans('em.month'),
                    onClick: () => {
                        this.event_date_range = [moment().startOf("month").toDate(), moment().endOf("month").toDate()]
                    }
                },
                {
                    text: trans('em.next')+' '+trans('em.month'),
                    onClick: () => {
                        this.event_date_range = [moment().add(1, 'months').startOf("month").toDate(), moment().add(1, 'months').endOf("month").toDate()]
                    }
                },
            ],
            //CUSTOM

            events    : [],
            event_id  : 0,

            //CUSTOM
            embed_code_modal : 0,
            embed_code       : null,
            online_location  : null,
            
            columns    : columns,
            sortKey    : 'created_at',
            sortOrders : sortOrders,
            length     : 10,
            search     : '',
            is_qrcode  : 0,
            qrcode_booking_id : 0,
            event_date_range : [],
            event_start_date : '',
            event_end_date   : '',
            qrcode_booking_id : 0,
            //CUSTOM
        }
    },

     computed: {
        current_page() {
            // get page from route
            if(typeof this.page === "undefined")
                return 1;
            return this.page;
        },
        
         //CUSTOM
        
        filteredBookings() {
            let bookings = this.bookings;
            if (this.search) {
                bookings = bookings.filter((row) => {
                    return Object.keys(row).some((key) => {
                        return String(row[key]).toLowerCase().indexOf(this.search.toLowerCase()) > -1;
                        })
                });
            }
            let sortKey = this.sortKey;

            let order = this.sortOrders[sortKey] || 1;
            if (sortKey) {
                bookings = bookings.slice().sort((a, b) => {
                let index = this.getIndex(this.columns, 'name', sortKey);
                        a = String(a[sortKey]).toLowerCase();
                        b = String(b[sortKey]).toLowerCase();
                        if (this.columns[index].type && this.columns[index].type === 'date') {
                            return (a === b ? 0 : new Date(a).getTime() > new Date(b).getTime() ? 1 : -1) * order;
                        } else if (this.columns[index].type && this.columns[index].type === 'number') {
                            return (+a === +b ? 0 : +a > +b ? 1 : -1) * order;
                        } else {
                            return (a === b ? 0 : a > b ? 1 : -1) * order;
                        }
                });
            }
            return bookings;
        },

        paginatedBookings() {
            
            return this.filteredBookings;
        }
        //CUSTOM
    },

    methods:{
          // get all events
        getOrganiserBookings() {

            if(typeof this.start_date === "undefined") {
                this.start_date     = '';
            }
            if(typeof this.end_date === "undefined") {
                this.end_date     = '';
            }

            //CUSTOM

            if(typeof this.event_start_date === "undefined") {
                this.event_start_date     = '';
            }
            if(typeof this.event_end_date === "undefined") {
                this.event_end_date     = '';
            }
            // axios.get(route('eventmie.obookings_organiser_bookings')+'?page='+this.current_page+'&event_id='+this.event_id+'&start_date='
            //              +this.start_date+'&end_date='+this.end_date)

            
            axios.get(route('eventmie.obookings_organiser_bookings')+'?page='+this.current_page+'&event_id='+this.event_id+'&start_date='
                         +this.start_date+'&end_date='+this.end_date+'&length='+this.length+'&search='+this.search+'&event_start_date='+this.event_start_date+'&event_end_date='+this.event_end_date)
            //CUSTOM
            .then(res => {
                this.currency   = res.data.currency;
                this.bookings   = res.data.bookings.data;
                this.pagination = {
                    'total' : res.data.bookings.total,
                    'per_page' : res.data.bookings.per_page,
                    'current_page' : res.data.bookings.current_page,
                    'last_page' : res.data.bookings.last_page,
                    'from' : res.data.bookings.from,
                    'to' : res.data.bookings.to
                };
            })
            .catch(error => {
                
            });
        },

        // view booking by organiser 
        organiserViewBooking(booking_id) {
            axios.get(route('eventmie.obookings_organiser_bookings_show',[booking_id]))
            .then(res => {
                if(res.data.status)
                {
                    this.getOrganiserBookings();
                }    
            })
            .catch(error => {
                
            });
        },

        // view booking
        goto_route(id) {
            return route('eventmie.obookings_organiser_bookings_show', {id:id});
        },

        // return route with event slug
        eventSlug(slug){
            
            if(slug)
            {
                return route('eventmie.events_show',[slug]);
            }    
        },

        // return route with download URL
        downloadURL(id, order_number) {
            if(id && order_number) {
                return route('eventmie.downloads_index',[id, order_number]);
            }
        },

        // searching by date 
        dateRange: function () {
            var is_date_null = 0;
            if(Object.keys(this.date_range).length > 0 )
            {
                // convert date range to server side date
                this.date_range.forEach(function(value, key) {
                    if(value != null) {
                        is_date_null = 1;

                        if(key == 0)
                            this.start_date   =  this.convert_date(value); // convert local start_date to server date then searching by date
                        
                        if(key == 1)
                            this.end_date     =  this.convert_date(value); // convert local end_date to server date then searching by date
                    }
                }.bind(this));
                
                // date reset
                if(is_date_null <= 0){
                    this.start_date  = '';
                    this.end_date    = '';
                }

                this.getOrganiserBookings()
            }
            
        },

        // get all events
        getMyEvents() {
            axios.get(route('eventmie.all_myevents'))
            .then(res => {
                this.events  = res.data.myevents;
            })
            .catch(error => {
                
            });
        },

        
         //CUSTOM

         // searching by date 
        eventDateRange: function () {
            var is_date_null = 0;
            if(Object.keys(this.event_date_range).length > 0 )
            {
                // convert date range to server side date
                this.event_date_range.forEach(function(value, key) {
                    if(value != null) {
                        is_date_null = 1;

                        if(key == 0)
                            this.event_start_date   =  this.convert_date(value); // convert local start_date to server date then searching by date
                        
                        if(key == 1)
                            this.event_end_date     =  this.convert_date(value); // convert local end_date to server date then searching by date
                    }
                }.bind(this));
                
                // date reset
                if(is_date_null <= 0){
                    this.event_start_date  = '';
                    this.event_end_date    = '';
                }

                this.getOrganiserBookings()
            }
            
        },
            
        sortBy(key) {
            this.sortKey = key;
            this.sortOrders[key] = this.sortOrders[key] * -1;
        },
        getIndex(array, key, value) {
            return array.findIndex(i => i[key] == value)
        },

        // reset pagination
        resetPagination: _.debounce(function(){
            this.my_page = 1;

            this.$router.push({ query: Object.assign({}, this.$route.query, {  page: 1 }) }).catch(()=>{});


            this.getOrganiserBookings();
        }, 1000),
        

        expandTableRow: function (e) {
            var element = e.target;
            var expand_table = document.getElementById('table-expand-'+element.id);
            expand_table.classList.toggle('hidden');
            if(element.innerText == trans('em.show_more')) {
                // expand
                element.innerText = trans('em.show_less');
            } else {
                // collapse
                element.innerText = trans('em.show_more');
            }
        },

        downloadInvoice(id = null){
            return route('invoice',[id]);
        }


        // CUSTOM

        
    },
   
    mounted() {
        this.getOrganiserBookings();
        this.getMyEvents();
        
        // send email after successful bookings
        this.sendEmail();
    },

    watch : {
        date_range: function () {
            this.dateRange();
        },

        //CUSTOM
        event_date_range: function () {
            this.eventDateRange();
        },
        //CUSTOM

        event_id: function () {
            this.getOrganiserBookings();
        },

    }
}
</script>


