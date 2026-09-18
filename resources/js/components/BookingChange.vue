<template>
<div>
    <h2 class="card-title text-center">{{ $t("message.change_booking") }}</h2>


    <ul>
        <li>
            <span class="fw-bold d-inline-block me-2">{{$t("message.address")}}:</span>
            <span>{{$store.state.address}}</span>
        </li>
        <li>
            <span class="fw-bold d-inline-block me-2">{{$t("message.phone_number")}}:</span>
            <span>{{$store.state.phone_number}}</span>
        </li>

    </ul>

    <div class="text-center mt-2">
        <a href="#"  class="btn btn-link" @click.prevent="prev('parent_select')">{{$t('message.prev')}}</a>
    </div>
</div>
</template>

<script>
export default {
    name: "BookingChange",
    props: {
        phone: {
            type: String,
            required: true,
            default: '+380677855392'
        },
        route: {
            type: String,
            required: true,
            default: 'atmosphera'
        },
    },
    data(){
        return{
            apiUrl: import.meta.env.VITE_APP_API_URL,
            apiKey: import.meta.env.VITE_APP_API_KEY,
            appName: import.meta.env.VITE_APP_NAME,
            bookings:[],
            // settings:[],
        }
    },

    created() {
        this.getBooking();
    },

    methods: {

        prev(type) {
            if ('parent_select' == type) {
                this.$emit('selectAction')
            } else {
                this.step = type;
            }
        },
        getBooking(){

            const headers = {
                'Content-Type': 'application/json',
                'API-Key': this.apiKey
            };
            axios.get(this.apiUrl+"api/getBooking", {
                headers,
                params: {
                    route: this.route,
                    phone: this.phone,
                }
            })
                .then(response => {
                    this.bookings=response.data.bookings;
                    // this.settings=response.data.settings;
                })
                .catch(error => {
                    console.error('Error:', error);
                });

        },

    }
}
</script>


