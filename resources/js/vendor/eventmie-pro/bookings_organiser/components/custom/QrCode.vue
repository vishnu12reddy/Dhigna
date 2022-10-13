<template>
    <div>
        <div class="modal modal-mask" v-if="is_qrcode > 0 && qrcode_booking_id > 0">
            <div class="modal-dialog modal-container">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                        <h3 class="title">{{ booking.ticket_title }} {{ ' x '+booking.quantity }}</h3>
                        <p class="subtitle text-center"><strong>#{{ order_number }}</strong></p>
                        <br>
                        <img :src="qrcode_file" class="mx-auto d-block img-responsive">
                    </div>
                    
                </div>
            </div>
        </div>
        
    </div>
</template>

<script>

import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';


export default {
    props: ["organizer_id", "is_qrcode", "qrcode_booking_id", "order_number", "booking"],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
           qrcode_file : null
             
        }
    },

    methods: {
        // reset form and close modal
        close: function () {    
            this.$parent.is_qrcode            = 0;
            this.$parent.qrcode_booking_id    = 0;
        },


        // get qrcode
        getQrcode(event) {
            // prepare form data for post request
            let post_url = route('get_qrcode');
           
            // axios post request
            axios.post(post_url,{
                booking_id : this.qrcode_booking_id, 
            })
            .then(res => {
                // on success
                // use vuex to update global sponsors array
                if(res.data.status)
                {
                    this.qrcode_file = res.data.qrcode_file;
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
    
    mounted() {
        this.getQrcode();   
    }


}
</script>