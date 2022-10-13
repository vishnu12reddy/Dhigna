<template>
    <div class="row">
        <div class="col-md-12">
        
            <div class="modal modal-mask" v-if="event_id > 0">
                <div class="modal-dialog modal-container">
                    <div class="modal-content lgx-modal-box">
                        <div class="modal-header">
                            <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                            <h3 class="title"> {{ trans('em.private_event') }}</h3>
                        </div>
                        
                        <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                            <input  type="hidden" class="form-control lgxname"  name="event_id" :value="event_id">
                            
                            <div class="modal-body">
                                <div class="form-group">
                                    <input type="checkbox" class="custom-control-input" :value=1 name="is_private" v-model="is_private">
                                    <label class="custom-control-label" > &nbsp;&nbsp;{{ trans('em.is_private') }}</label>
                                </div>

                                 <div class="form-group">
                                    <label for="event_password">{{ trans('em.event')+' '+trans('em.password') }}</label>
                                    <input type="text" class="form-control lgxname" v-model="event_password" name="event_password" >
                                </div>
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



import mixinsFilters from '../../../../../../../eventmie-pro/resources/js/mixins.js';

export default {

    

    props: ["event_id", 'event'],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
           event_password : null,
           is_private     : false
           
        }
    },


    methods: {

        // reset form and close modal
        close: function () {
            this.$refs.form.reset();
            this.$parent.event_id = 0;
            
            
        },

        edit(){
            console.log('hello');
            this.event_password   = this.event.event_password;
            this.is_private       = this.event.is_private;
            
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
            let post_url = route('private_event');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                
                this.close();
                this.showNotification('success',  trans('em.event')+'  '+trans('em.password')+' '+trans('em.saved')+' '+trans('em.successfully'));
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

        
    },

    mounted(){
       if(this.event_id > 0) {
            this.edit();
            
        }
    }
}
</script>