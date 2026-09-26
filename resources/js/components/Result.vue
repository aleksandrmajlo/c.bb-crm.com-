<template>
    <div class="reserv" v-cloak>
        <loading v-model:active="isLoading" :is-full-page="fullPage"/>
        <info-result></info-result>
        <div class="booking">
            <div class="title-h1">{{$t('your_order')}}</div>
            <a href="#" @click.prevent class="link-address" style="margin-bottom: 1rem;"><i class="icon icon-marker"></i>{{ address }}</a>
            <a href="#" @click.prevent class="link-address"><i class="icon icon-marker"></i>{{ phone_number }}</a>
            <div class="wrapOrders">
                <template v-for="(order,index) in orders" :key="index">
                    <div class="itemOrder">
                        <a  class="removeOrder" href="#" @click.prevent="removeOrder(order.booking_id)">
                            <i class="fas fa-trash"></i>
                        </a>
                        <div class="flex-row">
                            <div class="left">
                                <ul class="list js_cart_list">
                                    <li class="item main-item">
                                        <span class="js_day_item title-h3">{{ tableTitle(order) }}</span><br>
                                        <span class="js_day_item">{{ dateTitle(order) }} · {{ order.time.text }}</span>
                                        <span class="time">{{ dateDay(order) }} · 1:00 {{$t('hours')}}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <template v-if="total>0">
                <div class="result" style="margin-bottom: 1rem;border-top:none;">
                    <span class="title-h4">{{$t('all')}}:</span>
                    <span class="title-h4 sum">
                        <span style="display: inline-block;margin-right: 0.2rem;" class="js_total_cost">{{ total }}</span><span>грн</span>
                    </span>
                </div>
            </template>
            <div class="backWrap">
                <a href="#" @click.prevent="back" >{{$t('back')}}</a>
            </div>
        </div>
    </div>
</template>

<script>
import {mapState, mapActions,mapGetters} from 'vuex';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import Swal from 'sweetalert2'
import InfoResult from './InfoResult.vue';

export default {
    name: "Result",
    components: {Loading, InfoResult},
    data() {
        return {
            isLoading: false,
            fullPage: true,

            ifTotal: false,
            disabled_rasch: true,

        }
    },
    computed: {
        ...mapState({
            address: (state) => state.address,
            phone_number: (state) => state.phone_number,

            orders: (state) => state.orders,
            tables: (state) => state.tables,

            date_booking: (state) => state.date_booking,
            week: (state) => state.week,

            times: (state) => state.times,

            total: (state) => state.total,
        }),
        ...mapGetters([
            'isValidOrder',
        ])
    },
    created() {
        this.getTotal();
    },
    methods: {
        tableTitle(order) {
            return order.table_title || this.tables[order.table_id]?.title || '—';
        },
        dateTitle(order) {
            return order.date_title || this.week[order.date]?.title || order.date || '—';
        },
        dateDay(order) {
            return order.date_day || this.week[order.date]?.day || '';
        },
        removeOrder(booking_id){
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
                        setTimeout(()=>{
                            this.$store.dispatch('getBokingsDops').then(() => {
                                this.$store.dispatch('getTotalBookings').then(() => {
                                    this.isLoading = false;
                                })
                            });
                        },1000)
                    });

                } else if (result.isDismissed) {
                }
            })

        },
        getTotal() {
            this.isLoading = true;
            this.$store.dispatch('getTotalBookings').then(() => {
                this.isLoading = false;
            })
        },
        back(){
            this.$store.commit('stepGlobalSet', 'action_select');
        },
    }
}
</script>
