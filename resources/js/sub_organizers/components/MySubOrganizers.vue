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
                            <th>{{ trans('em.role') }}</th>
                            <th>{{ trans('em.delete') }}</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(sub_organizer, index) in sub_organizers" :key="index" >
                            
                            <td>{{ sub_organizer.name }} </td>
                            <td>{{ sub_organizer.email }} </td>
                            <td>{{ sub_organizer.role_name }} </td>
                           
                            <td>
                                <a href="#" class="lgx-btn lgx-btn-sm " @click="editSubOrganizer(sub_organizer)"><i class="fas fa-edit"></i> {{ trans('em.edit') }}</a>
                            </td>
                        </tr>
                    
                    </tbody>
                </table>
            </div>
        </div>

        <hr>
        <div class="row" v-if="sub_organizers.length > 0">
            <div class="col-md-12 text-center">
                <pagination-component v-if="pagination.last_page > 1" :pagination="pagination" :offset="pagination.total"  @paginate="getMySubOrganizers()"></pagination-component>
            </div>     
        </div>

        <edit-sub-organizer v-if="edit_sub_organizer > 0" :sub_organizer="sub_organizer" :edit_sub_organizer="edit_sub_organizer"></edit-sub-organizer>
    </div>
             
</template>

<script>

import PaginationComponent from '../../../../eventmie-pro/resources/js/common_components/Pagination'

import mixinsFilters from '../../../../eventmie-pro/resources/js/mixins.js';

import { VueRouter } from 'vue-router';

// import component for vue routes

import EditSubOrganizer from './EditSubOrganizer.vue';


export default {
    props: [
        // pagination query string
        'page',
        'category'
    ],

    components: {
        PaginationComponent,
        EditSubOrganizer,
        
    },

          
    mixins:[
        mixinsFilters
    ],

    data() {
        return {
            sub_organizers           : [],
            sub_organizer            : [],

            pagination: {
                'current_page': 1
            },
            
            edit_sub_organizer        : 0,

            
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
        
        // get all sub_organizers
        getMySubOrganizers() {
            axios.get(route('get_sub_organizers')+'?page='+this.current_page)
            .then(res => {
                
                this.sub_organizers  = res.data.sub_organizers.data;

                this.pagination = {
                    'total' : res.data.sub_organizers.total,
                    'per_page' : res.data.sub_organizers.per_page,
                    'current_page' : res.data.sub_organizers.current_page,
                    'last_page' : res.data.sub_organizers.last_page,
                    'from' : res.data.sub_organizers.from,
                    'to' : res.data.sub_organizers.to
                };
            })
            .catch(error => {
                
            });
        },

        // // edit myevents
        editSubOrganizer(sub_organizer = []) {
            this.sub_organizer = sub_organizer; 
            this.edit_sub_organizer  = 1;    
        },

        
    },
    mounted() {
        this.getMySubOrganizers();
    }
}
</script>
<style>

</style>