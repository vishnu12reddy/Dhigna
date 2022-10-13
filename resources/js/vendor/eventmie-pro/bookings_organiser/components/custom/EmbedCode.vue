<template>
        <div class="row" v-if="embed_code_modal > 0">
        <div class="col-md-12">
            <div class="modal modal-mask modal-big" >
                <div class="modal-dialog modal-container">
                    <div class="modal-content lgx-modal-box">
                        <div class="modal-header">
                            <button type="button" class="close" @click="close()"><span aria-hidden="true">&times;</span></button>
                            <h3 class="title">{{ trans('em.online_event') }} </h3>
                        </div>
                        
                        <div class="modal-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="iframe-full" v-html="embed_code"></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div v-html="online_location"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
<script>
export default {
     props: ["embed_code_modal","embed_code", "online_location"],

    data() {
        return {
            
        }
    },

    methods: {
        
        // reset form and close modal
        close: function () {
            this.$parent.embed_code_modal = 0;
            this.$parent.embed_code       = null;
            
        },

        
    },

    mounted(){
       
    }
}
</script>