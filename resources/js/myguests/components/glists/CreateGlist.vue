<template>
    <div class="row">
        <div class="col-md-12">
        
            <div class="modal modal-mask" v-if="create_glist > 0">
                <div class="modal-dialog modal-container">
                    <div class="modal-content lgx-modal-box">
                        <div class="modal-header">
                            <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                            <h3 class="title"> {{ trans('em.create')+' '+trans('em.guestlist') }}</h3>
                            
                        </div>
                        
                        <form ref="form" @submit.prevent="validateForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="glist_id" v-model="glist_id">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="name">{{ trans('em.name') }}<sup>*</sup></label>
                                    <input type="text" class="form-control lgxname" v-model="name" name="name" v-validate="'required'">
                                    <span v-show="errors.has('name')" class="help text-danger">{{ errors.first('name') }}</span>
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


import mixinsFilters from '../../../mixins.js';

export default {

    

    props: ['create_glist', 'glist'],

    mixins:[
        mixinsFilters
    ],

    data() {
        return {
           name     : null,
           glist_id : 0,
        }
    },


    methods: {

        // reset form and close modal
        close: function () {
            this.$refs.form.reset();
            this.$parent.create_glist = 0;
            this.$parent.glist = [];
            
            
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
            let post_url = route('create_glist');
            let post_data = new FormData(this.$refs.form);
            
            // axios post request
            axios.post(post_url, post_data)
            .then(res => {
                
                this.close();
                this.showNotification('success',  trans('em.guestlist')+' '+trans('em.saved')+' '+trans('em.successfully'));
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

        // edit glist
        editGlist(){
            if(Object.keys(this.glist).length > 0){
                this.name     = this.glist.name;
                this.glist_id = this.glist.id;
            }
        }
    },

    mounted(){
       this.editGlist();
    }
}
</script>