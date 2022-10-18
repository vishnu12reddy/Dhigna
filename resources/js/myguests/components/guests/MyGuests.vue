<template>
    <div class="container">
        <div class="row">
            
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('em.name') }}</th>
                            <th>{{ trans('em.email') }}</th>
                            <th>{{ trans('em.delete') }}</th>
                            <th>{{ trans('em.edit') }}</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(guest, index) in guests" :key="index" >
                            
                            <td>{{ guest.name }} </td>
                            <td>{{ guest.email }} </td>
                           
                            <td>
                                <a href="#" class="lgx-btn lgx-btn-sm lgx-btn-danger" @click="deleteGuest(guest.id)"><i class="fas fa-trash-alt"></i> {{ trans('em.delete') }}</a>
                            </td>

                            <td>
                                <a href="#" class="lgx-btn lgx-btn-sm lgx-btn-info" @click="edit_guest = 1, edit_guest_data = guest"><i class="fas fa-edit"></i> {{ trans('em.edit') }}</a>
                            </td>

                        </tr>
                    
                    </tbody>
                </table>
            </div>
        </div>
        <edit-guest v-if="edit_guest > 0" :edit_guest="edit_guest" :guest="edit_guest_data" ></edit-guest>

        <hr>
        <div class="row" v-if="guests.length > 0">
            <div class="col-md-12 text-center">
                <pagination-component v-if="pagination.last_page > 1" :pagination="pagination" :offset="pagination.total"  @paginate="getMyGuests()"></pagination-component>
            </div>     
        </div>
    </div>
             
</template>

<script>

import PaginationComponent from '../../../../../eventmie-pro/resources/js/common_components/Pagination'

import mixinsFilters from '../../../mixins.js';

import EditGuest from './EditGuest.vue';

import { VueRouter } from 'vue-router';

export default {
    props: [
        // pagination query string
        'page',
        'category'
    ],

    components: {
        PaginationComponent,
        EditGuest

    },
    
    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            guests           : [],
            guest            : [],

            pagination: {
                'current_page': 1
            },
            
            add_glist        : 0,
            add_guest        : 0,

            glist_id         : glist_id,

            edit_guest       : 0,
            edit_guest_data  : [],
        }
    },
    
    computed: {
        current_page() {
            // get page from route
            if(typeof this.page === "undefined")
                return 1;
            
            return this.page;
        },
    },
    methods: {
        
        // get all guests
        getMyGuests() {
            axios.get(route('get_myguests')+'?page='+this.current_page+'&glist_id='+this.glist_id)
            .then(res => {
                
                this.guests  = res.data.myguests.data;

                this.pagination = {
                    'total' : res.data.myguests.total,
                    'per_page' : res.data.myguests.per_page,
                    'current_page' : res.data.myguests.current_page,
                    'last_page' : res.data.myguests.last_page,
                    'from' : res.data.myguests.from,
                    'to' : res.data.myguests.to
                };
            })
            .catch(error => {
                
            });
        },

        // // edit myevents
        editGuest(guest = []) {
            this.guest = guest; 
            this.add_guest  = 1;    
        },

        //delete glist

        deleteGuest(guest_id = 0){

            if(guest_id <= 0)
                return true;

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {

                    axios.post(route('delete_guest'),{
                        guest_id : guest_id,
                    })
                    .then(res => {
                        this.showNotification('success',  trans('em.delete')+' '+trans('em.successfully'));
                        // reload page   
                        setTimeout(function() {
                            location.reload(true);
                        }, 1000);
                    })
                    .catch(error => {
                        
                    });
                    
                }
            })

            
        }
        

    },
    mounted() {
        this.getMyGuests();
    }
}
</script>
<style>

</style>