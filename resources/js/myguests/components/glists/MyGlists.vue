<template>
    <div class="container">
        <div class="row">
             <div class="col-md-6">
                <button type="button" class="lgx-btn lgx-btn-sm lgx-btn-red" @click="() => {create_glist = 1;}">
                    <span><i class="fas fa-calendar-plus"></i> {{ trans('em.create') }} {{ trans('em.guestlist') }}</span>
                </button>  
                <create-glist v-if="create_glist > 0" :create_glist="create_glist" :glist="glist" ></create-glist>
            </div>

            <div class="col-md-6 pull-right text-right">
                <button type="button" class="lgx-btn lgx-btn-red lgx-btn-sm" @click="() => {add_guest = 1;}">
                    <span><i class="fas fa-calendar-plus"></i> {{ trans('em.create') }} {{ trans('em.guest') }}</span>
                </button>  
            </div>
            <add-guest v-if="add_guest > 0" :add_guest="add_guest" ></add-guest>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('em.name') }}</th>
                            <th>{{ trans('em.total') }} {{ trans('em.guests') }}</th>
                            <th>{{ trans('em.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(glist, index) in glists" :key="index" >
                            <td>{{ glist.name }}</td>
                            <td>{{ glist.guests.length }}</td>
                            <td>
                                <div class="btn-group-vertical">
                                    <button class="btn lgx-btn lgx-btn-black lgx-btn-sm" type="button" @click="editGlist(glist)"><i class="fas fa-edit"></i> {{ trans('em.edit') }}</button>
                                    <a class="btn lgx-btn lgx-btn-sm" :href="exportGuestEmails(glist.id)" ><i class="fas fa-edit"></i> {{ trans('em.export')+' '+trans('em.email') }} </a>
                                    <a class="btn lgx-btn lgx-btn-sm"  :href="viewGuests(glist.id)"><i class="fas fa-eye"></i> {{ trans('em.view') }} {{ trans('em.guests') }}</a>
                                    <a class="btn lgx-btn lgx-btn-danger lgx-btn-sm" href="#" @click="deleteGlist(glist.id)"><i class="fas fa-trash-alt"></i> {{ trans('em.delete') }}</a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <hr>
        <div class="row" v-if="glists.length > 0">
            <div class="col-md-12 text-center">
                <pagination-component v-if="pagination.last_page > 1" :pagination="pagination" :offset="pagination.total" :path="'myglists'" @paginate="getMyGlists()"></pagination-component>
            </div>     
        </div>
    </div>
             
</template>

<script>

import PaginationComponent from '../../../../../eventmie-pro/resources/js/common_components/Pagination'

import mixinsFilters from '../../../mixins.js';

import CreateGlist from './CreateGlist';
import AddGuest from '../guests/AddGuest';


export default {
    props: [
        // pagination query string
        'page',
        'category'
    ],

    components: {
        PaginationComponent,
        CreateGlist,
        AddGuest,
        
      
    },
    
    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            glists           : [],
            
            // for create glist
            create_glist     : 0,
            
            // for add to guestlist
            add_guest        : 0,

            // for send email
            glist_id         : 0,

            // for edit glist
            glist            : [],

            pagination: {
                'current_page': 1
            },
            
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
        
        // get all glist with pagination
        getMyGlists() {
            axios.get(route('pagination_myglist')+'?page='+this.current_page)
            .then(res => {
                
                this.glists  = res.data.myglists.data;

                this.pagination = {
                    'total' : res.data.myglists.total,
                    'per_page' : res.data.myglists.per_page,
                    'current_page' : res.data.myglists.current_page,
                    'last_page' : res.data.myglists.last_page,
                    'from' : res.data.myglists.from,
                    'to' : res.data.myglists.to
                };
            })
            .catch(error => {
                
            });
        },

        // edit glist
        editGlist(glist = []){
            this.glist        = glist;
            this.create_glist = 1;

        },

        //delete glist

        deleteGlist(glist_id = 0){

            if(glist_id <= 0)
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

                    axios.post(route('delete_glist'),{
                        glist_id : glist_id,
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

            
        },

        // return route with event slug
        viewGuests(id){
            return route('myguests_index', [id]);
        },

        exportGuestEmails($glist_id = null){
            return route('export_emails', [$glist_id])
        }

    },
    mounted() {
        this.getMyGlists();
    }
}
</script>
<style>

</style>