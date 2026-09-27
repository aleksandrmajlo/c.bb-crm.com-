<template>
    <section class="table-block">
        <loading v-model:active="isLoading" :is-full-page="fullPage"/>
        <div class="wrapper">
            <div class="container">
                <div class="schedule-heading bottom-m">
                    <div class="title-h2">{{$t('change_date')}}</div>
                    <a v-if="route === 'utc'"
                       href="/club/utc.jpg"
                       class="club-scheme-link"
                       @click.prevent="showClubScheme">
                        <i class="fas fa-map-marked-alt" aria-hidden="true"></i>
                        <span>{{$t('club_scheme')}}</span>
                    </a>
                </div>
                <div class="card__tabs">
                    <ul class="tub_header">
                        <li v-for="(date,key) in week" class="item " :class="key==date_booking ? 'active' : ''">
                            <button @click.prevent="setDate(key)" class="tab-btn">{{ date.title }}<span class="day">{{ date.day }}</span></button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="status-cell status-cell_my " style="position: relative;">
            <span class="caption free">{{$t('free')}} </span>
            <span class="caption reserved">{{$t('busy')}}</span>
        </div>
        <div class="table__main" id="table__main">
            <div class="list-unstyled-h">
                <ul class="list-unstyled">
                    <li v-for="(date,key) in week" class="item " :class="key==date_booking ? 'init' : ''">
                       <a href="#" @click.prevent="setDate(key)">{{ date.title }} ({{ date.day }})</a> <i class="icon icon-caret"></i>
                    </li>
                </ul>
            </div>
            <div class="container container-table">
                <div class="main_cont">
                    <div style="display: flex;" class="tub_body active">
                        <div data-module="sticky-table" class="table">
                            <div class="schedule-scroll flex swiper js-base-slider" aria-label="Розклад майданчиків" tabindex="0">
                                <ul class='parent-th swiper-wrapper  wrapShulde'>
                                    <li class="th swiper-slide">
                                        <ul class="second-level">
                                            <li class="td" v-for="(table,key,index) in tables" :key="index">
                                                <a href="#" class="link link--sticky link--sticky_my " :title="table.title">
                                                    <span style=" " class="field-sign">
                                                        {{ table.title }}
                                                    </span>
                                                </a>
                                                <ul class="date third-level syncscroll">
                                                    <template v-for="(time,kk,ind) in times[table.id]" :key="ind">
                                                        <li   v-if="time.time_passed"  class="td-busy-not">
                                                            <div class="time busy-time" >
                                                                <a href="#" class="link" @click.prevent="addOrder(table.id,time)">
                                                                   <span class="time_text">{{ time.text }}</span>
                                                                 </a>
                                                            </div>
                                                        </li>
                                                        <li class="td-busy" v-else  >
                                                            <div class="time busy-time" :style="'background-color:' + time.color">
                                                                <span class="time_text">{{ time.text }}</span>
                                                                <span v-if="time.trainer" class="trainer_text"> {{ time.trainer }}</span>
                                                            </div>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </li>
                                        </ul>
                                        <ul class="tr-mobile">
                                            <li class="caption #f21825"></li>
                                        </ul>
                                    </li>
                                </ul>

                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>
</template>

<script>
import {mapState, mapActions} from 'vuex';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';
import Swal from 'sweetalert2';

export default {
    name: "SportsDays",
    components: {
        Loading,
    },
    data() {
        return {
            isLoading: false,
            fullPage: false,
        }
    },
    created() {
        this.$store.dispatch('getTablesBookings').then(() => {})
    },
    computed: {
        ...mapState({
            orders: (state) => state.orders,
            route: (state) => state.route,
            date_booking: (state) => state.date_booking,
            week: (state) => state.week,
            tables: (state) => state.tables,
            times: (state) => state.times,
        }),
    },
    methods: {
        showClubScheme() {
            Swal.fire({
                title: this.$t('club_scheme'),
                imageUrl: '/club/utc.jpg',
                imageAlt: this.$t('club_scheme'),
                width: 'min(1200px, 95vw)',
                confirmButtonText: this.$t('close'),
                confirmButtonColor: '#f58220',
                customClass: {
                    popup: 'club-scheme-popup',
                },
            });
        },
        setDate(date_booking) {
            this.isLoading = true;
            this.$store.commit('date_bookingSet', date_booking);
            this.$store.dispatch('getTablesBookings').then(() => {
                this.isLoading = false;
                $('.list-unstyled').removeClass('open-list')
            })
        },

        addOrder(table_id, time) {
            this.$store.commit('orderSet', {
                table_id:table_id,
                employment_id:null,
                tennis_options:[],
                time:time,
                primary:true,
                booking_id:null,
            });
            $('#main').addClass('main-background');
            this.$store.commit('stepGlobalSet', 'employment_options');
        }
    }
}
</script>

<style scoped>
.schedule-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.club-scheme-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    flex: 0 0 auto;
    padding: 0.65rem 1rem;
    border-radius: 0.5rem;
    background: #f58220;
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 0.25rem 0.75rem rgba(245, 130, 32, 0.3);
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.club-scheme-link:hover,
.club-scheme-link:focus {
    background: #d96d0e;
    color: #fff;
    transform: translateY(-1px);
}

:global(.club-scheme-popup .swal2-image) {
    width: auto;
    max-width: 100%;
    max-height: 72vh;
    object-fit: contain;
}

@media (max-width: 767px) {
    .schedule-heading {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
