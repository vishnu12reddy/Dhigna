<template>
    <div>
        <h4 class="lead-caption">#{{ quantity_index+1 }} {{trans('em.attendee')}}</h4>
        <div class="row" >
            <div class="col-md-3">
                <input type="text" class="form-control"  style="height: auto !important;" :name="'name_'+ticket_index+'_'+quantity_index" v-model="$parent.name[ticket_index][quantity_index]" v-validate="'required'" :placeholder="trans('em.name')+'*'">
                <span 
                    v-show="errors.has('name_'+ticket_index+'_'+quantity_index)" 
                    class="help text-danger"
                >Attendee name required</span>
            </div>

            <div class="col-md-3">
                <input type="number" class="form-control" style="height: auto !important;" :name="'phone_'+ticket_index+'_'+quantity_index" v-model="$parent.phone[ticket_index][quantity_index]" v-validate="'required'" :placeholder="trans('em.phone')+'*'">
                <span 
                    v-show="errors.has('phone_'+ticket_index+'_'+quantity_index)" 
                    class="help text-danger"
                >Attendee phone required</span>
            </div>

            <div class="col-md-6">
                <textarea class="form-control" rows="1" style="height: auto !important;" :name="'address_'+ticket_index+'_'+quantity_index" v-model="$parent.address[ticket_index][quantity_index]" v-validate="'required'" :placeholder="trans('em.address')+'*'"></textarea>
                <span 
                    v-show="errors.has('address_'+ticket_index+'_'+quantity_index)" 
                    class="help text-danger"
                >Attendee address required</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    inject: ['$validator'],
    
    props:['quantity_index', 'ticket_index'],
    
    methods:{
        
    },
    beforeDestroy() {
        // remove value from array on addendees dropdown decrement
        this.$parent.name[this.ticket_index].splice(-1,1);
        this.$parent.phone[this.ticket_index].splice(-1,1);
        this.$parent.address[this.ticket_index].splice(-1,1);
    }

}
</script>