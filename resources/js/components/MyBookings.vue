<template>
    <div class="reserv reserv_center" v-cloak>
        <loading v-model:active="isLoading" :is-full-page="fullPage"/>
        <div class="booking">
            <div class="title-h1">{{$t('my_booking')}}</div>
            <div class="myBookingsWrap">
                <div class="booking_item" v-for="(booking,index) in bookings" :key="index">
                    <a class="removeOrder" href="#" @click.prevent="removeBooking(booking.id)">
                        <i class="fas fa-trash"></i>
                    </a>
                    <ul class="list js_cart_list">
                        <li class="item main-item">
                            <span class="js_day_item title-h3">{{ booking.table_title }}</span>
                            <span class="js_day_item title-h4">{{ booking.employment_title }}</span>
                            <span class="js_day_item">{{ booking.date }} · {{ booking.time }}</span>
                            <span class="time">{{ booking.day }} · 1:00 {{$t('hours')}}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div style="margin-bottom: 1rem;text-align: center">
                <a href="#" class="back-button" @click.prevent="back">{{$t('add_new_bookikg')}}</a>
            </div>
        </div>
    </div>
</template>

<script>
import {mapState, mapActions, mapGetters} from 'vuex';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import Swal from 'sweetalert2'

export default {
    name: "MyBookings",
    components: {Loading},
    data() {
        return {

            isLoading: false,
            fullPage: false,
        }
    },
    computed: {
        ...mapState({
            address: (state) => state.address,
            phone_number: (state) => state.phone_number,
            phone: (state) => state.phone,

            bookings: (state) => state.bookings,
        })
    },
    created() {
        this.$store.dispatch('getBokings').then(() => { });
    },
    methods: {
        back() {
            this.$store.commit('stepGlobalSet', 2);
        },
        removeBooking(booking_id) {

            Swal.fire({
                text: this.$t('booking_delete'),
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: this.$t('delete'),
                cancelButtonText: this.$t('cancel'),
            }).then((result) => {
                if (result.isConfirmed) {

                    this.isLoading=true;
                    this.$store.dispatch('removeBooking',booking_id).then(() => {
                        this.$store.dispatch('getBokings').then(() => {
                            this.isLoading=false;
                        });
                    });

                } else if (result.isDismissed) {
                }
            })


        },
    }
}
</script>


