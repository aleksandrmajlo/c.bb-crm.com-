<template>
    <div class="cont-info step" v-cloak>
        <loading v-model:active="isLoading" :is-full-page="fullPage"/>
        <div class="title-h3 js_user_phone">{{phone}}</div>

        <div class="prolong js_discount_bloc js_prolong" style="display: block;">
            <div class="title-h3" style="max-width: initial">{{$t('dops_time_m')}}:</div>
            <div class="prolong-wrap">
                <div class="table__main table__main_checkout" id="table__main">
                    <div class=" co_ntainer-table">

                        <div class="list-unstyled-f" style="margin-bottom: 2rem">
                            <ul class="list-unstyled-fields list-fields-checkout dates_uns">
                                <li v-for="(date,key,index) in week"  class="item " :class="key==date_booking ? 'init' : ''">
                                    <a href="#" @click.prevent="setDate(key)">{{ date.title }} ({{ date.day }})</a> <i class="icon icon-caret"></i>
                                </li>
                            </ul>
                        </div>

                        <div class="list-unstyled-f" style="margin-bottom: 2rem">
                            <ul class="list-unstyled-fields list-fields-checkout tables_uns">
                                <li  v-for="(table,key,index) in tables" class="item " :class="key==table_dop_id ? 'init' : ''">
                                    <a href="#" @click.prevent="setTable_dop(key)">{{table.title}}</a>
                                    <i class="icon icon-caret"></i>
                                </li>
                            </ul>
                        </div>

                        <!--            ****************************************************************            -->
                        <div  class="ma_in_cont main_cont_checkout " id="main_cont_checkout">
                            <div class="slick-list draggable my_slick-list">

                                <div class="wrap_checkout ">
                                    <div class="base_checkout">
                                        <ul class="">
                                            <template v-for="(time,kk) in times_dop"   :key="kk">

                                                <li  v-if="isOrderItem(time)&&kk<=halfDops"  class="td-busy" >
                                                    <label>
                                                        <div class="prolong-date" @click="setOrderDop(time)">
                                                            <span class="subtitle">{{week[date_booking].title}} · {{ time.text }}</span>
                                                            <span class="time">{{week[date_booking].day}} · 1:00 години </span>

                                                            <span class="title-h4">
                                                                 <span class="js_pro_long_cost">{{$t('mu_booking')}}</span>
                                                            </span>

                                                        </div>
                                                    </label>
                                                </li>

                                                <li class="activeLinkWrap"  v-else-if="time.time_passed&&kk<=halfDops" >
                                                    <label  @click="setOrderDop(time)">
                                                        <div class="prolong-date">
                                                            <span class="subtitle">{{week[date_booking].title}} · {{ time.text }}</span>
                                                            <span class="time">{{week[date_booking].day}} · 1:00 години </span>
                                                            <span>

                                                            </span>
                                                        </div>
                                                    </label>
                                                </li>

                                                <li v-else-if="kk<=halfDops" class="td-busy">
                                                    <label>
                                                        <div class="prolong-date">
                                                            <span class="subtitle">{{week[date_booking].title}} · {{ time.text }}</span>
                                                            <span class="time">{{week[date_booking].day}} · 1:00 години  </span>
                                                        </div>
                                                        <span class="title-h4">
                                                            <span class="js_pro_long_cost">{{$t('busy')}}</span>
                                                        </span>

                                                    </label>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>

                                <div class="wrap_checkout ">
                                    <div class="base_checkout">

                                        <ul class="">
                                            <template v-for="(time,kk) in times_dop" :key="kk">

                                                <li  v-if="isOrderItem(time)&&kk>halfDops"  class="td-busy" >
                                                    <label>
                                                        <div class="prolong-date" @click="setOrderDop(time)">
                                                            <span class="subtitle">{{week[date_booking].title}} · {{ time.text }}</span>
                                                            <span class="time">{{week[date_booking].day}} · 1:00 години </span>

                                                            <span class="title-h4">
                                                                 <span class="js_pro_long_cost">{{$t('mu_booking')}}</span>
                                                            </span>

                                                        </div>
                                                    </label>
                                                </li>
                                                <li class="activeLinkWrap" v-else-if="time.time_passed&&kk>halfDops" >
                                                    <label  @click="setOrderDop(time)">
                                                        <div class="prolong-date">
                                                            <span class="subtitle">{{week[date_booking].title}} · {{ time.text }}</span>
                                                            <span class="time">{{week[date_booking].day}} · 1:00 години</span>
                                                        </div>
                                                    </label>
                                                </li>
                                                <li class="td-busy" v-else-if="kk>halfDops">
                                                    <label>
                                                        <div class="prolong-date">
                                                            <span class="subtitle">{{week[date_booking].title}} · {{ time.text }}</span>
                                                            <span class="time">{{week[date_booking].day}} · 1:00 {{$t('hours')}}</span>
                                                        </div>
                                                        <span class="title-h4"><span class="js_pro_long_cost">{{$t('busy')}}</span></span>
                                                    </label>
                                                </li>
                                            </template>
                                        </ul>


                                    </div>
                                </div>


                            </div>
                        </div>
                        <!--            ****************************************************************            -->
                    </div>

                </div>
            </div>
        </div>

        <div class="checkbox-accept js_valid_checkbox input-container pass">
            <input type="checkbox" v-model="accept" id="accept" class="focus-pay">
            <label for="accept" style="visibility: visible;">
                <span class="info">{{$t('pod_order')}}<a href="#" @click.prevent="" class="link-g">{{$t('umovu_pub')}}</a></span>
            </label>
        </div>

        <div class="checkbox-agree js_valid_checkbox input-container pass">
            <input type="checkbox"  id="agree" v-model="agree" class="focus-pay">
            <label for="agree" style="visibility: visible;">
                <span class="info">{{$t('agree_text')}}</span>
            </label>
        </div>
        <button :disabled="disabled" @click.prevent="pay" type="submit" class="btn btn-red">{{$t('Confirm')}}</button>

    </div>
</template>
<script>
import {mapState, mapActions,mapGetters} from 'vuex';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import Swal from 'sweetalert2'

export default {
    name: "InfoResult",
    components: {Loading},
    data() {
        return {
            agree:true,
            accept:true,
            isLoading: false,
            fullPage: 1,
        }
    },
    computed: {
        disabled(){
            let r=true;
            if(this.agree&&this.accept){
                r=false;
            }
            return r
        },
        ...mapState({
            address: (state) => state.address,
            phone_number: (state) => state.phone_number,
            phone: (state) => state.phone,

            orders: (state) => state.orders,
            tables: (state) => state.tables,
            times: (state) => state.times,
            employment_id: (state) => state.employment_id,
            tennis_options: (state) => state.tennis_options,

            date_booking: (state) => state.date_booking,
            week: (state) => state.week,

            table_dop_id: (state) => state.table_dop_id,
            times_dop: (state) => state.times_dop,


            total: (state) => state.total,
        }),
        ...mapGetters([
            'isValidOrder','halfDops',
        ]),

    },
    created() {
        this.orders.forEach((el,index )=> {
            if(el.primary){
                this.$store.commit('table_dop_idSet', el.table_id);
            }
        });
        if (!this.table_dop_id) {
            return;
        }
        this.isLoading = true;
        this.$store.dispatch('getBokingsDops').then(() => {
            this.isLoading = false;
        });
    },
    methods:{
        setDate(date_booking) {
            this.isLoading = true;
            this.$store.commit('date_bookingSet', date_booking);
            $('.list-unstyled-fields').removeClass('open-list');
            this.$store.dispatch('getBokingsDops').then(() => {
                this.isLoading = false;
            })
        },
        setTable_dop(table_id){
            this.isLoading = true;
            this.$store.commit('table_dop_idSet', table_id);
            $('.list-unstyled-fields').removeClass('open-list');
            this.$store.dispatch('getBokingsDops').then(() => {
                this.isLoading = false;
            })
        },
        setOrderDop(time) {
            if (!this.table_dop_id) {
                return;
            }
            this.isLoading=true;
            this.$store.commit('orderSet', {
                table_id:this.table_dop_id,
                employment_id:this.employment_id,
                tennis_options:this.tennis_options,
                time:time,
                primary:false,
                booking_id:null,
            });
             this.$store.dispatch('addBooking').then(()=>{
                setTimeout(()=>{
                    this.$store.dispatch('getBokingsDops').then(() => {
                        this.$store.dispatch('getTotalBookings').then(() => {
                            this.isLoading = false;
                        })
                    });
                },2000)
             })

        },
        pay() {
            this.$store.commit('stepGlobalSet', 'select_pay');
        },
        isOrderItem(time){
            let b=false;
            let start_time=time.start;
            let end_time=time.end;
            this.orders.forEach((el,index )=> {
                if(el.date==this.date_booking&&this.table_dop_id==el.table_id&&el.time.start==start_time&&el.time.end==end_time){
                     b=true;
                }
            });
            return b;
        }
    },

}
</script>
<style >
.main_cont_checkout .status-cell {
    display: -webkit-box;
    display: -webkit-flex;
    display: -ms-flexbox;
    display: flex;
    position: absolute;
    top: 0px;
    z-index: 999;
    background: #fff;
    min-height: 32px;
}

.table__main.table__main_checkout {
    background: none;
    max-width: 95%;
    margin: 0 2%;
}
.slick-list {
    margin: 0 -10px;
}
.slick-slide > div {
    padding: 0 10px;
}
.slick-prev:before, .slick-next:before {
    color: black;
}
.slick-arrow.slick-prev {
    left: -26px;
}
.main_cont_checkout .slick-slide {
    opacity: 0;
    transition: opacity 0.6s ease-in-out;

}
.main_cont_checkout .slick-active {
    opacity: 1;
    transition: opacity 0.6s ease-in;
}
.main_cont_checkout.slick-cloned {
    opacity: 0.6;
}
.main_cont_checkout .slick-list {
    height: 520px;
    overflow-y: auto;
}
.main_cont_checkout .slick-arrow.slick-next {
    right: -40px;
}
.td-busy label {
    background: #b3b3b3;
}
span.day-time {
    font-size: 17px;
    font-weight: 800;
    display: block;
    margin: 0 0 10px 5px;
}
li.td-busy label .js_pro_long_cost {
    color: #ca3232;
}
li.td-busy label:hover {
    color: #ca3232;
}
.timer-block {
    flex-direction: row-reverse;
    font-weight: 600;
}
.lock-timer {
    max-width: 32px;
}
.lock-timer {
    max-width: 17px;
    display: inline;
    position: relative;
    top: 2px;
    fill: red;
}
@media (max-width: 1024px) {
    .main_cont_checkout.slick-initialized.slick-slider {
        margin-left: 15px;
    }
}
@media (max-width: 650px) {
    .table__main.table__main_checkout {
        max-width: 100%;

    }
}
div#table__main {
    max-width: 100%;
}
.ma_in_cont.main_cont_checkout.slick-slider {
    margin-bottom: 80px;
    width: 100%;
}
.list-unstyled-h {
    position: relative;
    min-height: 100px;
}
</style>
