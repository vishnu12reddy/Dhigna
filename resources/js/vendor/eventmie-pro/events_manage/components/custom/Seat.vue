<template>
    <div>
        <update-seat v-if="update > 0 " :data="data"></update-seat> 

        <br><br>
        <div class="row">
            <div class="col-md-12">
                <h5>{{ trans('em.upload_seatchart_info') }}</h5>
                <table>
                    <tr>
                        <td class="text-right"><p>{{ trans('em.seat_max') }}: </p></td>
                        <td><p> &nbsp;{{ ticket.quantity }}</p></td>
                    </tr>
                    <tr>
                        <td class="text-right"><p>{{ trans('em.seat_added') }}: </p></td>
                        <td><p> &nbsp;{{ seats.length }} </p></td>
                    </tr>
                </table>
            </div>
        </div>
        <br><br>

        <!-- Seating chart image -->
        <div class="row">
            <div class="col-md-12">
                <div class="seat-container">
                    <img @click="seatsSeletion" :src="'/storage/'+local_ticket.seatchart.chart_image" class="seat-img">
                    <span class="seat-mark" 
                        :class="{'seat-disabled': (seat.status <= 0 && seat.status != null)}"
                        :style="{ 'left': seat.x, 'top' :seat.y }" 
                        @click="updateSeatName(seat)"
                        v-for="(seat, index) in seats"
                        v-bind:key="index"
                    >{{seat.name}}</span>
                </div>
            </div>
        </div>  

        <br><br>
        <div class="row">
            <div class="col-md-12">
                <form id="form" ref="form" method="POST" @submit.prevent="validateForm">
                    <input  type="hidden" class="form-control lgxname"  name="seatchart_id" 
                    :value="local_ticket.seatchart.id">
                    <input  type="hidden" class="form-control lgxname"  name="ticket_id" :value="local_ticket.id">
                    <input type="hidden" class="form-control lgxname"  name="event_id" :value="local_ticket.event_id">
                    
                    <input type="hidden" name="coordinates[]"
                            v-for="(seat, index) in seats"
                            :key="index+10000"
                            :value="seat.x+','+seat.y"
                    >

                    <input type="hidden" name="ids[]"
                            v-for="(seat, index) in seats"
                            :key="index+20000"
                            :value="seat.id"
                            
                    >

                    <input type="hidden" name="seat_names[]"
                            v-for="(seat, index) in seats"
                            :key="index+30000"
                            :value="seat.name"
                            
                    >
                        
                    <button type="submit" class="lgx-btn lgx-btn-success btn-block">{{trans('em.save_seat')}}</button>

                    <button v-if="ticket.seatchart != null" type="button" class="lgx-btn lgx-btn-sm btn-block" :class="ticket.seatchart.status > 0 ? 'lgx-btn-danger' : 'lgx-btn-info'" @click="seatchartDisableEnable(ticket.id, ticket.seatchart.status)">{{ (ticket.seatchart.status > 0 ? trans('em.disable') : trans('em.enable')) }} {{ trans('em.seatchart') }}</button>
                </form>
            </div>
        </div>  

    </div>

</template>
<script>
import UpdateSeat from './UpdateSeat';
import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';

export default {
    props:['ticket'],
    mixins:[
        mixinsFilters
    ],

    data()  {
         
        return {
            local_ticket : this.ticket,
            seats   : [],
            count   : 0,
            update  : 0,
            data    : {},
        }
        
    },

    components:{
        UpdateSeat,
    },

    methods: {

        //select seats
        seatsSeletion(e) {
            // add seats upto ticket.quantity
            if(this.seats.length < this.ticket.quantity) {
                // increment count
                this.count = this.count + 1;
                
                //create seat
                let seat = {
                    'x'           : (e.offsetX-10)+'px',
                    'y'           : (e.offsetY-10)+'px',
                    'name'        : this.count,
                    'id'          : null,
                    'ticket_id'   : this.local_ticket.id,
                    'status'      : null,
                };

                // created seat push into seats array
                this.seats.push(seat);
            } else {
                this.showNotification('error', trans('em.seat_max_error'));
            }
            
        },

        //save seat 
        saveSeat(){
            let formData = new FormData(this.$refs.form);

            axios.post(route('save_seats'),
                formData
            ).then(res => {

                if(res.data.status){
                    //ticket update
                    this.local_ticket = res.data.ticket
                    this.showSelectedSeats();
                    this.showNotification('success', trans('em.seat_saved'));
                }

            })
            .catch(error => {
                let serrors = Vue.helpers.axiosErrors(error);
                if (serrors.length) {
                    this.serverValidate(serrors);
                }
            });
        },

        //show selected seats

        showSelectedSeats(){
            this.seats = [];
            this.count = 0;
            let _this  = this;

            // check seats
            if(this.local_ticket.seatchart != null && this.local_ticket.seatchart.seats.length > 0){

                this.local_ticket.seatchart.seats.forEach((value, index) => {
                    
                    //increment count variable and check is number or not
                    if(!isNaN(value.name))
                        _this.count            = parseInt(value.name);

                    // comma saprated value convert into array    
                    let coordinates        = value.coordinates.split(',');    

                    // create seat
                    let seat = {
                        'x'           : coordinates[0],
                        'y'           : coordinates[1],
                        'name'        : value.name,
                        'id'          : value.id,
                        'ticket_id'   : _this.local_ticket.id,
                        'status'      : value.status,
                    };

                    // created seat push into seats array
                    _this.seats.push(seat);
                    
                });

            }
        },

        // open UpdateSeat modal
        updateSeatName(seat = null){
            this.data   = seat;
            this.update = 1;
        },

        // validate data on form submit
        validateForm(event) {
            this.$validator.validateAll().then((result) => {
                if (result) {
                    this.saveSeat(event);            
                }
            });
        },

        // show server validation errors
        serverValidate(serrors) {
            this.$validator.validateAll().then((result) => {
                this.$validator.errors.add(serrors);
            });
        },
        
        
        //CUSTOM
        // seatchart disable or enable
        seatchartDisableEnable(ticket_id = null, status){
            this.showConfirm().then((res) => {
                if(res) {
                    axios.post(route('disable_enable_seatchart'), {
                        ticket_id       : ticket_id,
                    })
                    .then(res => {
                    
                        if(res.data.status) {
                            this.showNotification('success', (status > 0 ? trans('em.seatchart_disabled') : trans('em.seatchart_enabled')));
                            // reload page   
                            setTimeout(function() {
                                location.reload(true);
                            }, 1000);
                        }else{
                            this.showNotification('error', res.data.error);
                        }
                    })
                    .catch(error => {
                        Vue.helpers.axiosErrors(error);
                    });
                }
            })
        },
        //CUSTOM
    },

    mounted() {
        this.showSelectedSeats();
    }   

}
</script>
