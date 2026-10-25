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
                                        <span class="time">{{ dateDay(order) }} · {{ durationLabel(order) }}</span>
                                        <span v-if="order.employment_title" class="order-detail">
                                            {{ order.employment_title }}
                                        </span>
                                        <span v-if="order.employment_price_label" class="order-detail">
                                            {{ order.employment_price_label }}
                                        </span>
                                        <span v-if="order.trainer_id" class="order-detail">
                                            {{$t('trainer')}}: {{ order.trainer_title }} —
                                            {{ money(order.trainer_price) }} грн/год
                                            <template v-if="order.price_breakdown">
                                                · {{ money(order.price_breakdown.trainer_price) }} грн
                                            </template>
                                        </span>
                                        <span
                                            v-for="option in optionDetails(order)"
                                            :key="`${order.uniqueString}-${option.key || option.id}`"
                                            class="order-detail order-detail--option"
                                        >
                                            {{$t('ten_opt')}}: {{ option.title }}
                                            <template v-if="Number(option.quantity) > 1">× {{ option.quantity }}</template>
                                            <template v-if="option.total !== null && option.total !== undefined">
                                                — {{ money(option.total) }} грн
                                            </template>
                                            <template v-else>
                                                — {{ money(option.unit_price) }} грн/год
                                            </template>
                                        </span>
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
            tennis_options_catalog: (state) => state.tennis_options_catalog,

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
        durationLabel(order) {
            const start = Number(order.time?.start || 0);
            const end = Number(order.time?.end || 0);
            const minutes = Math.max(0, Math.round((end - start) * 60));

            return `${minutes} хв`;
        },
        money(value) {
            return Number(value || 0).toLocaleString('uk-UA', {
                maximumFractionDigits: 2,
            });
        },
        optionDetails(order) {
            if (Array.isArray(order.option_details) && order.option_details.length) {
                return order.option_details;
            }

            const selection = order.tennis_options || {};
            const quantities = {};
            if (Array.isArray(selection)) {
                selection.forEach((key) => {
                    quantities[String(key)] = (quantities[String(key)] || 0) + 1;
                });
            } else {
                Object.entries(selection).forEach(([key, quantity]) => {
                    quantities[String(key)] = Number(quantity || 1);
                });
            }

            return Object.entries(quantities).map(([key, quantity]) => {
                const option = this.tennis_options_catalog[key] || {};
                return {
                    key,
                    title: option.title || key,
                    quantity,
                    unit_price: Number(option.price || 0),
                    total: null,
                };
            });
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

<style scoped>
.order-detail {
    display: block;
    margin-top: 4px;
    color: #667085;
    font-size: 14px;
}

.order-detail--option {
    color: #344054;
}
</style>
