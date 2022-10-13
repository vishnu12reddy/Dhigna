<template>
    <div class="row">
                    
        <div class="col-12 col-sm-6 col-lg-4" 
            v-match-heights="{
                el: ['h5.sub-title'],  // Array of selectors to fix
            }"
            v-for="(event, index) in events" 
            :key="index"
        >
            <div class="lgx-event">
                <a :href="eventSlug(event.slug)" >

                    <!-- simple events means without repetitive who Upcomming-->
                    <div class="lgx-event__tag" 
                        v-if="!event.repetitive && moment().format('YYYY-MM-DD') < userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')"
                    >
                        <span>{{countDays(moment().format("YYYY-MM-DD"), userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD'))-1}} {{ trans('em.days_left') }} </span>
                        <span>{{ trans('em.upcoming') }}</span>
                    </div>

                    <!-- simple events means without repetitive who today-->
                    <div class="lgx-event__tag" 
                        v-if="!event.repetitive && moment().format('YYYY-MM-DD') == userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')"
                    >
                        <span>{{ trans('em.today') }}</span>
                        <span>{{ trans('em.event') }}</span>
                    </div>

                     <!-- simple events means without repetitive who ended-->
                    <div class="lgx-event__tag" v-if="!event.repetitive && moment().format('YYYY-MM-DD') > 
                            userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')"
                    >
                        <span>{{ trans('em.ended') }}</span>
                        <span>{{ trans('em.event') }}</span>
                    </div>

                    
                    <!-- repetitive events who Upcoming  -->
                    <div class="lgx-event__tag" v-if="event.repetitive && moment().format('YYYY-MM-DD') < userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')"
                    >
                        <span>{{countDays(moment().format("YYYY-MM-DD"), userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD'))-1}} {{ trans('em.days_left') }} </span>
                        <span >{{ trans('em.upcoming') }}</span>
                    </div>
                    
                     <!-- repetitive events who Started -->
                    <div class="lgx-event__tag" v-if="event.repetitive && moment().format('YYYY-MM-DD') >= userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')     && moment().format('YYYY-MM-DD') <= userTimezone(event.end_date+' '+event.end_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')"
                    >
                        <span>{{ changeDateFormat(moment(userTimezone(event.end_date+' '+event.end_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD'))) }}</span>
                        <span >{{ trans('em.started') }}</span>
                    </div>

                     <!-- repetitive events who Ended -->
                    <div class="lgx-event__tag" v-if="event.repetitive && moment().format('YYYY-MM-DD') > userTimezone(event.end_date+' '+event.end_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD')">
                        <span >{{ trans('em.event') }}</span>
                        <span>{{ trans('em.ended') }}</span>
                    </div>
                     
                     
                    <!-- online event -->
                    <div class="lgx-event__online" v-if="event.online_location">
                        <span><i class="fas fa-signal"></i> {{ trans('em.online') }}</span>
                        <span>{{ trans('em.event') }}</span>
                    </div>

                    <div class="lgx-event__image">
                        <img :src="'/storage/'+event.thumbnail" alt="">
                    </div>

                    <div class="lgx-event__info">
                        <div class="lgx-event__featured" v-if="event.repetitive">
                            
                            <span v-if="event.repetitive_type == 1">{{ trans('em.repetitive_daily')  }}</span>
                            <span v-if="event.repetitive_type == 2">{{ trans('em.repetitive_weekly') }}</span>
                            <span v-if="event.repetitive_type == 3">{{ trans('em.repetitive_monthly') }}</span>
                        </div>

                        <div class="lgx-event__featured-left"
                            v-if="checkFreeTickets(event.tickets)"
                        >
                            <span>{{ trans('em.free') }}</span>
                        </div>

                        <div class="meta-wrapper">
                            <span> {{changeDateFormat(userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD'), "YYYY-MM-DD")}}</span>
                            <span v-if="event.start_date != event.end_date">{{ changeDateFormat(userTimezone(event.end_date+' '+event.end_time, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD'), "YYYY-MM-DD")}} </span>
                            <span v-else>{{ changeTimeFormat(userTimezone(event.start_date+' '+event.start_time, 'YYYY-MM-DD HH:mm:ss').format(date_format.vue_time_format) )}} - {{ changeTimeFormat(userTimezone(event.end_date+' '+event.end_time, 'YYYY-MM-DD HH:mm:ss').format(date_format.vue_time_format) )}} {{ showTimezone() }}</span>
                            <span>{{event.city}}</span>
                        </div>
                        
                        <h3 class="title">{{ event.title }}</h3>
                        <h5 class="sub-title" v-if="event.excerpt">{{ event.excerpt }}</h5>
                        <h5 class="sub-title text-primary">@{{ event.venue}}</h5>

                        
                    </div>
                        <!-- CUSTOM -->
                    <div class="row lgx-event__info " v-if="event.sale_tickets.length > 0">
                          
                        <div class="col-md-12" v-if="event.sale_tickets[0].sale_start_date != null">
                            <div class="row" v-if="
                                userTimezone(event.sale_tickets[0].sale_start_date, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD HH:mm:ss') <= moment().tz(Intl.DateTimeFormat().resolvedOptions().timeZone).format('YYYY-MM-DD HH:mm:ss') && 
                                userTimezone(event.sale_tickets[0].sale_end_date, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD HH:mm:ss') > moment().tz(Intl.DateTimeFormat().resolvedOptions().timeZone).format('YYYY-MM-DD HH:mm:ss')">
                                
                                <div class="col-xs-4">
                                    <h3 class="title"> {{ trans('em.on_sale') }}
                                    </h3>
                                </div>

                                <div class="col-xs-8">
                                    <p class="title">
                                        <i class='fas fa-clock fa-spin' style="color:red"></i>
                                        <vue-countdown :time="timerOnSale(event.sale_tickets[0].sale_start_date, event.sale_tickets[0].sale_end_date)" v-slot="{ days, hours, minutes, seconds }">
                                        {{ days }} {{ trans('em.days') }}, {{ hours }} : {{ minutes }} : {{ seconds }} {{ trans('em.left') }}
                                        </vue-countdown>
                                    </p>
                                   
                                </div>

                                

                            </div>
                        </div>
                    </div>  
                    <!-- CUSTOM -->

                    <!-- CUSTOM -->
                    <!-- <div class="lgx-event__footer"> -->
                    <div class="lgx-event__footer" v-if="event.sale_tickets.length <= 0">
                    <!-- CUSTOM -->
                        <div 
                            v-for="(ticket, index1) in event.tickets" 
                            :key="index1" 
                            v-if="index1 <= 1"
                        >
                            <!-- {{ticket.title}} : {{ (ticket.price <= 0) ? trans('em.free') : ticket.price+' '+currency }} -->

                              <!-- CUSTOM -->
                            {{ticket.title}} : {{ (ticket.price <= 0) ? trans('em.free') : ticket.price+' '+(event.currency != null ? event.currency : currency) }}
                            <!-- CUSTOM -->
                        </div>
                    </div>

                    
                    <!-- CUSTOM -->
                    <div class="lgx-event__footer" v-else>
                        <div 
                            v-for="(ticket, index1) in event.sale_tickets" 
                            :key="index1" 
                            v-if="index1 <= 1"
                        >
                            <!-- {{ticket.title}} : {{ (ticket.price <= 0) ? trans('em.free') : ticket.price+' '+currency }} -->
                           
                            {{ticket.title}} : <span><del>
                                {{  (ticket.price <= 0) ? trans('em.free') : ticket.price+' '+(event.currency != null ? event.currency : currency) }}
                            </del></span>
                            
                            <span>
                            {{ (ticket.sale_price <= 0) ? trans('em.free') : ticket.sale_price+' '+(event.currency != null ? event.currency : currency) }}
                            </span>

                        </div>
                    </div>
                    <!-- CUSTOM -->

                    <div class="lgx-event__category">
                        <span>{{ event.category_name }}</span>
                    </div>
                </a>
            </div>
        </div>

        <div class="col-12" v-if="not_found">
            <h4 class="heading text-center mt-30"><i class="fas fa-exclamation-triangle"></i> {{ trans('em.events_not_found') }}</h4>
        </div>
        
    </div>
</template>

<script>

import mixinsFilters from '../../../mixins.js';

//  CUSTOM
import VueCountdown from '@chenfengyuan/vue-countdown';
//  CUSTOM


export default {
    
    //  CUSTOM
    components: {
        VueCountdown
    },
    //  CUSTOM

    props: ['events', 'currency', 'date_format'],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            not_found: false,
        }
    },

    methods:{
        
        // check free tickets of events
        checkFreeTickets(event_tickets = []){
            let free = false;
            event_tickets.forEach(function(value, key) {
                if(parseFloat(value.price) <= parseFloat(0))
                {
                    free = true;
                }
            });    
            return free;
        },

        
        // return route with event slug
        eventSlug: function eventSlug(slug) {
            return route('eventmie.events_show', [slug]);
        },

         //CUSTOM
        timerOnSale(sale_start_date = null, sale_end_date = null){

            if(sale_start_date == null || sale_end_date == null)
                return true;
            
            var local_tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            
            var a    = this.userTimezone(sale_end_date, 'YYYY-MM-DD HH:mm:ss').format('DD/MM/YYYY HH:mm:ss');
            var b    = moment().tz(local_tz).format('DD/MM/YYYY HH:mm:ss');
            var ms   = 0; // milliseconds
            
            if(moment(a,"DD/MM/YYYY HH:mm:ss") > moment(b,"DD/MM/YYYY HH:mm:ss")){
                ms = moment(a,"DD/MM/YYYY HH:mm:ss").tz(local_tz).diff(moment(b,"DD/MM/YYYY HH:mm:ss").tz(local_tz)); //milliseconds
                
            }
           
            return ms;
        },

  
    },

    watch: {
        events: function () {
            this.not_found = false;
            if(this.events.length <= 0)
                this.not_found = true;
        },
    },

}
</script>