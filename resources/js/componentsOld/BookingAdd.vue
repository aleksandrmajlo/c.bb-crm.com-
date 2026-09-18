<template>
<div>
    <loading v-model:active="isLoading"  :is-full-page="fullPage"/>
    <template v-if=" step=='sports'">
        <h2 class="card-title text-center">{{ $t("message.game_selection") }}</h2>
        <p class="text-center">{{ $t("message.game_selection_type") }}</p>
        <div id="accordion">
            <div class="card card_accordion"  v-for="(sport,index) in sports" :key="index">
                <div class="card-header" :id="'headingOne_'+index" style="background:  #808080;">
                    <h5 class="mb-0">
                        <button style="text-decoration: none;" class="btn btn-link colBtn" data-toggle="collapse" :data-target="'#collapse_'+index" aria-expanded="true" aria-controls="collapseOne">
                            <img style="max-width: 30px;height: auto;" :src="sport.icon">
                            <span class="d-inline-block " style="margin-left: 1rem;color: #fff;"> {{sport.title}}</span>
                        </button>
                    </h5>
                </div>
                <div :id="'#collapse_'+index" class="collapse"
                     :class="index==1 ? 'show' : ''"
                     aria-labelledby="headingOne" data-parent="#accordion">
                    <div class="card-body">
                        <div class="d-flex flex-wrap">
                            <div class="form-check me-2" v-for="(employment,ind)  in sport.employments" :key="ind"  >
                                <input class="form-check-input" type="radio" name="employment_id" v-model="employment_id" :value="sport.id+'_'+employment.id" :id="'employment_'+index+'_'+ind">
                                <label class="form-check-label" :for="'employment_'+index+'_'+ind">
                                    {{employment.title}}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <button :disabled="disabled_time"  @click.prevent="next('time')" class="btn btn-success w-100">{{$t('message.next')}}</button>
        </div>
        <div class="text-center mt-2">
            <a href="#"  class="btn btn-link" @click.prevent="prev('parent_select')">{{$t('message.prev')}}</a>
        </div>
    </template>

    <template v-else-if="step=='time'">

        <h2 class="card-title text-center">{{ $t("message.glub_schedule") }}</h2>
        <div class="  text-center ">
            <span>{{$store.state.working_mode_text}}</span>
        </div>
        <p class="text-center">{{ $t("message.date_select") }}</p>
        <div>
            <div class="mb-3">
                <label  class="form-label">{{ $t("message.date") }}</label>
                <vue-date-picker  v-model="date"
                                  locale="uk"
                                  text-input auto-apply
                                  format="dd-MM-yyyy"  :enable-time-picker="false" >
                </vue-date-picker>
            </div>
            <time-select v-show="isTimeSelect"></time-select>

            <!--
            <div   class="mb-3">
                <label  class="form-label">{{ $t("message.start") }}</label>
                <VueDatePicker v-model="start_time" time-picker />
            </div>
            <div class="mb-3">
                <label  class="form-label">{{ $t("message.end") }}</label>
                <VueDatePicker v-model="end_time" time-picker />
            </div>
               -->

        </div>
        <div class="text-center mt-3">
            <button :disabled="disabled_result"  @click.prevent="next('result')" class="btn btn-success w-100">{{$t('message.next')}}</button>
        </div>
        <div class="text-center mt-2">
            <a href="#"  class="btn btn-link" @click.prevent="prev('sports')">{{$t('message.prev')}}</a>
        </div>

    </template>

    <template v-else-if="step=='result'">
        <h2 class="card-title text-center">{{ $t("message.result_title") }}</h2>
        <ul>
            <li>
                <span class="fw-bold d-inline-block me-2">{{$t("message.address")}}:</span>
                <span>{{$store.state.address}}</span>
            </li>
            <li>
                <span class="fw-bold d-inline-block me-2">{{$t("message.phone_number")}}:</span>
                <span>{{phone}}</span>
            </li>
            <li>
                <span class="fw-bold d-inline-block me-2">{{$t("message.sport")}}:</span>
                <span>{{sportDatas.sport}}</span>
            </li>
            <li>
                <span class="fw-bold d-inline-block me-2">{{$t("message.employment")}}:</span>
                <span>{{sportDatas.employment}}</span>
            </li>
            <li>
                <span class="fw-bold d-inline-block me-2">{{$t("message.date")}}:</span>
                <span>{{dateTitle}}</span>
            </li>
            <li>
                <span class="fw-bold d-inline-block me-2">{{$t("message.time")}}:</span>
                <span>{{time_booking.text}}</span>
            </li>
        </ul>

        <div class="text-center mt-3">
            <button :disabled="disabled_pay"  @click.prevent="pay" class="btn btn-success w-100">{{$t('message.pay')}}</button>
        </div>
        <div class="text-center mt-2">
            <a href="#"  class="btn btn-link" @click.prevent="prev('time')">{{$t('message.prev')}}</a>
        </div>

    </template>

<!--

    <pre style="border:5px solid red; margin: 10px;padding: 10px;">{{tableId_employmentId}}</pre>
    <pre style="border:5px solid; margin: 10px;padding: 10px;">{{date_booking}}</pre>
    <pre style="border:5px solid blue; margin: 10px;padding: 10px;">{{time_booking}}</pre>

      -->


</div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/css/index.css';

import moment from 'moment';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import { mapState, mapActions } from 'vuex';

import TimeSelect from './TimeSelect.vue'

export default {
    name: "BookingAdd",
    components: {
        VueDatePicker,
        Loading,
        TimeSelect
    },

    data(){
        return {
            step:"sports",
            // step:"time",
            // step:"result",
            apiUrl: import.meta.env.VITE_APP_API_URL,
            apiKey: import.meta.env.VITE_APP_API_KEY,
            appName: import.meta.env.VITE_APP_NAME,
            appTest: import.meta.env.VITE_APP_TEST,

            sports:[],
            // settings:[],

            employment_id:null,

            date: new Date(),

            isTimeSelect:false,

            disabled_time:true,
            disabled_pay:false,

            isLoading: false,
            fullPage: false,

        }
    },
    created() {
        // test !!!!!!!!!!!!!!!
        if(typeof  this.appTest!="undefined"&&this.appTest==1){
            this.$store.commit('phoneSet','+380677855392');
        }
        this.getSport();
        $(document).on('click', '.colBtn', function(event){
            $('.show').removeClass('show');
            $(this).parents('.card_accordion').find('.collapse').addClass('show')
        });

    },
    watch:{
        employment_id(newV,oldV){
            this.disabled_time=false;
            this.$store.commit('tableId_employmentIdSet',newV);
            this.getTimeThDate(false);
        },
        date:{
            handler(newV, oldValue) {
                let date=newV;
                if(newV){
                    date=moment(newV).format('YYYY-MM-DD');
                }
                this.$store. commit('date_bookingSet', date);
                this.$store.commit('time_bookingSet',{type:null,text:'',start:null,end:null});
                this.getTimeThDate();
            },
            immediate: true
        }
    },

        disabled_result() {
            if(this.date&&this.time_booking.start&&this.time_booking.end) return false;
            return true;
        },
        sportDatas(){
            let res={
                'sport':"",
                'sport_id':"",
                'employment':'',
                'employment_id':'',
            };
            if(this.employment_id){
                let arr = this.employment_id.split("_");
                res.sport=this.sports[arr[0]].title;
                res.sport_id=this.sports[arr[0]].id;
                res.employment_id=arr[1];
                this.sports[arr[0]].employments.forEach((currentValue, index) =>{
                    if(currentValue.id==arr[1]){
                        res.employment=currentValue.title;
                    }
                });
            }
            return res;
        },
        dateTitle(){
            let title='';
            if(this.date) {
                title=moment(this.date).format('DD-MM-YYYY');
            }
            return title;
        },

    },
    methods:{
        next(type){
            this.step=type;
        },
        prev(type){
            if('parent_select'==type){
                // step:"sports",
                this.$emit('selectAction')
                console.log(type)
            }else{
                this.step=type;
            }
        },
        getSport(){
            const headers = {
                'Content-Type': 'application/json',
                'API-Key': this.apiKey
            };
            axios.get(this.apiUrl+"api/getSport", {
                headers,
                params: {
                    route: this.route,
                }
            })
                .then(response => {
                    this.sports=response.data.sports;
                    // this.settings=response.data.settings;
                })
                .catch(error => {
                    console.error('Error:', error);
                });

        },

        getTimeThDate(isLoading=true){
            if(isLoading){
                this.isLoading=true;
            }
            this.$store.dispatch('getBookingsDateTime').then(() => {
                this.isLoading=false;
                if(this.date_booking){
                    this.isTimeSelect=true;
                }else{
                    this.isTimeSelect=false;
                }
            })
        }

    }
}
</script>

