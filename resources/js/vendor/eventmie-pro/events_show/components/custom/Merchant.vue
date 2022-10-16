<template >
    <div class="row">
        <div class="col-md-6">
            <input type="text" id="name" class="form-control"  placeholder="Name" name="name" v-model="name" style="color:black">
        </div>

        <div class="col-md-6">
            <input type="text" id="email" class="form-control"  placeholder="Email" name="email" v-model="email" style="color:black">
        </div>

        <div class="col-md-12 mt-2">
            <div class="d-block">
                <input type="text" id="address" class="form-control"  placeholder="Address" name="address" v-model="address" style="color:black">
            </div>
        </div>
       
    </div>
  
</template>

<script type="text/javascript">
var profilename = $('#profilename').val();
var profileemail = $('#profileemail').val();
var profileaddress = $('#profileaddress').val();

export default {
    data() {
            return {
                name : profilename,
                email : profileemail,
                address : profileaddress,
            }
        },
   
    computed:{
        
    },
    mounted() {
        
    }
   
}
</script>


